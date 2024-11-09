<?php

namespace App\Http\Controllers;

use App\Enums\TransportVehicle;
use App\Enums\UserChoicesEnum;
use App\Enums\UserRole;
use App\Models\GuestBuyer;
use App\Models\GuestCustomer;
use App\Models\ProductsByBuyer;
use App\OrderItems;
use App\Orders;
use App\Products;
use App\Qty;
use App\Services\DriverFairServices;
use App\Services\GoogleMapServices;
use App\Services\ImageServices;
use App\Services\JsonResponseServices;
use App\Services\OrderServices;
use App\Services\ProductServices;
use App\User;
use App\Services\TwilioSmsService;
use App\Services\VerificationCodeServices;
use App\VerificationCodes;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stripe\Service\Climate\OrderService;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Inserts a newly arrived order
     * @author Muhammad Abdullah Mirza
     */
    public function new(Request $request)
    {
        if ($request->has('type')) {
            if ($request->type == 'delivery') {
                $rules = [
                    /* Order details */
                    'type' => 'required|string',
                    'items' => 'required|array',
                    'houseNo' => 'required|string',
                    'deliveryCharges' => 'required|numeric',
                    'serviceCharges' => 'required|numeric',
                    'device' => 'sometimes',
                    'paymentIntentId' => 'required|string',
                    /* Customer details */
                    'fName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
                    'lName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
                    'email' => 'required|email|max:255',
                    'countryCode' => 'required|string|max:4',
                    'phone' => 'required|string|max:13',
                    'fullAddress' => 'required|string',
                    'unitAddress' => 'nullable|string',
                    'country' => 'required|string|max:70',
                    'state' => 'required|string|max:70',
                    'city' => 'required|string|max:70',
                    'postcode' => 'nullable|string|max:11',
                    'lat' => 'required|numeric|between:-90,90',
                    'lon' => 'required|numeric|between:-180,180',
                ];
            } elseif ($request->type == 'self-pickup') {
                $rules = [
                    'type' => 'required|string',
                    'paymentIntentId' => 'required|string',
                ];
            }
        } else {
            return JsonResponseServices::getApiValidationFailedResponse(json_decode('{"type": ["The type field is required."]}'));
        }

        $validatedData = Validator::make($request->all(), $rules);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $buyer = User::getBuyerByEmail($request->email);
        if (! $buyer) {
            $guestBuyer = GuestBuyer::addOrUpdate(
                $request->fName,
                $request->lName,
                $request->email,
                $request->countryCode,
                $request->phone,
                $request->fullAddress,
                $request->unitAddress,
                $request->country,
                $request->state,
                $request->city,
                $request->postcode,
                $request->lat,
                $request->lon
            );
        }

        $createdById = $buyer?->id ?? $guestBuyer->id;
        $createdByType = $buyer?->getMorphClass() ?? $guestBuyer->getMorphClass();

        $groupedSellers = [];
        foreach ($request->items as $item) {
            Qty::subtractProductQty($item['sellerId'], $item['productId'], $item['qty']);

            $product = Products::getOnlyProductDetailsById($item['productId']);
            $groupedSellers[$item['sellerId']][] = [
                'productId' => $item['productId'],
                'qty' => $item['qty'],
                'userChoice' => $item['userChoice'],
                'sellerId' => $item['sellerId'],
                'price' => Products::getProductPrice($item['productId']),
                'volume' => $product->height * $product->width * $product->length,
                'weight' => $product->weight,
            ];
        }
        $orderArr = [];
        foreach ($groupedSellers as $sellerId => $order) {
            $totalWeight = OrderServices::getTotalWeight($order);
            $totalVolumn = OrderServices::getTotalVolumn($order);
            $totalItems = OrderServices::getTotalItems($order);
            $orderTotal = OrderServices::getOrderTotal($order);
            /* Adding amount into seller's wallet */
            User::addIntoWallet($sellerId, $orderTotal);

            if ($request->type == 'delivery') {
                $seller = User::getUserByID($sellerId, [
                    'business_phone',
                    'lat',
                    'lon'
                ]);
                $driverCharges = OrderServices::getDriverCharges(
                    $seller->lat,
                    $seller->lon,
                    $request->lat,
                    $request->lon,
                    $totalWeight,
                    $totalVolumn
                );
            }
            /* Create order */
            $orderId = Orders::add(
                $createdByType,
                $createdById,
                $sellerId,
                $orderTotal,
                $totalItems,
                $driverCharges ?? 0.00,
                $request
            )->id;
            /* Insert order items */
            foreach ($order as $orderItem) {
                OrderItems::add(
                    $orderId,
                    $orderItem['productId'],
                    $orderItem['price'],
                    $orderItem['qty'],
                    UserChoicesEnum::from($orderItem['userChoice'])
                );
            }

            if ($request->type == 'delivery') {
                $verificationCode = VerificationCodeServices::generateCode();
                VerificationCodes::add($orderId, $verificationCode);

                $newOrderApiEndPoint = '/api/orders/new';
                if (
                    url()->current() == config('constants.LIVE_DASHBOARD_URL') . $newOrderApiEndPoint ||
                    url()->current() == config('constants.APIS_DOMAIN_URL') . $newOrderApiEndPoint
                ) {
                    OrderServices::sendBulkSms(
                        $seller,
                        $request->phone,
                        $orderId,
                        $verificationCode
                    );
                }
            }

            $orderArr[] = $orderId;
        }

        if ($request->walletFlag == 1) User::deductFromWallet($createdById, $request->walletDeductionAmount);

        return JsonResponseServices::getApiResponse(
            $this->getOrdersFromIds($orderArr),
            config('constants.TRUE_STATUS'),
            config('constants.ORDER_PLACED_SUCCESSFULLY'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function orderProductByBuyer(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'sellerId' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn(Builder $query) => $query
                        ->whereIn('role_id', [UserRole::SELLER, UserRole::CHILD_SELLER])),
            ],
            'productName' => 'required|string|max:255',
            'qty' => 'required|integer',
            'maxPrice' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'partNumber' => 'nullable|string|max:255',
            'colors' => 'nullable|array',
            'transportVehicle' => [
                'required',
                Rule::in(array_column(TransportVehicle::cases(), 'value')),
            ],
            'featureImg' => 'nullable|image|max:2048',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'fName' => 'required|string|max:100',
            'lName' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'countryCode' => 'required|string|max:4',
            'phone' => 'required|string|max:13',
            'fullAddress' => 'nullable|string',
            'unitAddress' => 'nullable|string',
            'country' => 'nullable|string|max:70',
            'state' => 'nullable|string|max:70',
            'city' => 'nullable|string|max:70',
            'postcode' => 'nullable|string|max:11',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $buyer = User::getBuyerByEmail($request->email);
        if (! $buyer) {
            $guestBuyer = GuestBuyer::addOrUpdate(
                $request->fName,
                $request->lName,
                $request->email,
                $request->countryCode,
                $request->phone,
                $request->fullAddress,
                $request->unitAddress,
                $request->country,
                $request->state,
                $request->city,
                $request->postcode,
                $request->lat,
                $request->lon
            );
        }

        $createdById = $buyer?->id ?? $guestBuyer->id;
        $createdByType = $buyer?->getMorphClass() ?? $guestBuyer->getMorphClass();

        if ($request->has('colors')) {
            $colors = ProductServices::jsonEncodeColors($request->colors);
        }

        if ($request->hasFile('featureImg')) {
            $fileName = ImageServices::uploadImg($request, 'featureImg', $createdById);
        }
        /* Create the cutomer given product */
        $productByBuyer = ProductsByBuyer::add(
            $createdByType,
            $createdById,
            $request->sellerId,
            $request->productName,
            $request->qty,
            $request->maxPrice,
            $request->weight,
            $request->brand,
            $request->partNumber,
            $colors,
            $request->transportVehicle,
            $fileName ?? null,
            $request->height,
            $request->width,
            $request->length
        );
        /* Place order against the above product */
        $totalVolumn = $productByBuyer->height * $productByBuyer->width * $productByBuyer->length;
        $sellerId = $request->sellerId;
        $orderTotal = $request->maxPrice;
        $totalWeight = $request->weight;
        $totalItems = $request->qty;

        if ($request->type == 'delivery') {
            $seller = User::getUserByID($sellerId, [
                'business_phone',
                'lat',
                'lon'
            ]);
            $driverCharges = OrderServices::getDriverCharges(
                $seller->lat,
                $seller->lon,
                $request->lat,
                $request->lon,
                $totalWeight,
                $totalVolumn
            );
        }
        /* Create order */
        $orderId = Orders::add(
            $createdByType,
            $createdById,
            $sellerId,
            $orderTotal,
            $totalItems,
            $driverCharges ?? 0.00,
            $request
        )->id;
        /* Insert order items */
        OrderItems::add(
            $orderId,
            $productByBuyer->id,
            $productByBuyer->maxPrice,
            $productByBuyer->qty,
            UserChoicesEnum::SEND_TO_OTHER_STORES
        );
        
        if ($request->type == 'delivery') {
            $verificationCode = VerificationCodeServices::generateCode();
            VerificationCodes::add($orderId, $verificationCode);

            $newOrderApiEndPoint = '/api/orders/new';
            if (
                url()->current() == config('constants.LIVE_DASHBOARD_URL') . $newOrderApiEndPoint ||
                url()->current() == config('constants.APIS_DOMAIN_URL') . $newOrderApiEndPoint
            ) {
                OrderServices::sendBulkSms(
                    $seller,
                    $request->phone,
                    $orderId,
                    $verificationCode
                );
            }
        }

        $orderArr[] = $orderId;

        if ($request->walletFlag == 1) User::deductFromWallet($createdById, $request->walletDeductionAmount);

        return JsonResponseServices::getApiResponse(
            $this->getOrdersFromIds($orderArr),
            config('constants.TRUE_STATUS'),
            config('constants.ORDER_PLACED_SUCCESSFULLY'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * @author Huzaifa Haleem
     */
    public function showLoggedinBuyerOrders(Request $request)
    {
        try {
            $orders = Orders::select('id')->where('customer_id', '=', Auth::id())->orderByDesc('id');
            if (!empty($request->order_status)) $orders = $orders->where('order_status', '=', $request->order_status);
            $orders = $orders->paginate(20);
            $pagination = $orders->toArray();
            if (!$orders->isEmpty()) {
                $order_data = [];
                foreach ($orders as $order) $order_data[] = $this->getOrderDetails($order->id);
                unset($pagination['data']);
                return JsonResponseServices::getApiResponseExtention(
                    $order_data,
                    config('constants.TRUE_STATUS'),
                    '',
                    'pagination',
                    $pagination,
                    config('constants.HTTP_OK')
                );
            }
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function productsOfRecentOrder(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'productsLimit' => 'required|integer',
                'sellerId' => 'required|integer'
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->error());
            }

            $order = Orders::getRecentOrderByCustomerId(Auth::id(), $request->productsLimit, $request->sellerId);
            if (!empty($order)) {
                $recentOrderProdsData = [];
                foreach ($order->products as $product) $recentOrderProdsData[] = Products::getProductInfo(
                    $request->sellerId,
                    $product->id,
                    Products::getCommonColumns(),
                );
                /*
                * Just creating this variable so we don't have to call the "empty()" function again & again
                * Which will obviouly reduce the API response speed
                */
                $dataIsEmpty = empty($recentOrderProdsData);
                return JsonResponseServices::getApiResponse(
                    ($dataIsEmpty) ? [] : $recentOrderProdsData,
                    ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                    ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
                    config('constants.HTTP_OK'),
                );
            }

            return JsonResponseServices::getApiResponse(
                [],
                false,
                config('constants.NO_RECORD'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                false,
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * List all ready or delivered orders
     * for a specific delivery boy
     * @author Huzaifa Haleem
     */
    public function sellerOrders(Request $request)
    {
        $lat = \auth()->user()->lat;
        $lon = \auth()->user()->lon;
        $orders = [];
        if ($request->has('order_status') && $request->order_status == 'delivered') {
            $orders = Orders::query();
            $orders = $orders->where('order_status', '=', 'delivered');
            $orders = $orders->orderByDesc('created_at')->paginate();
            $pagination = $orders->toArray();
        } elseif ($request->has('order_status') && $request->order_status == 'ready') {
            $assignedOrders = Orders::where('driver_id', \auth()->id())->where('delivery_status', 'assigned')->get();
            if (count($assignedOrders) == 0) {
                $users = DB::table("users")
                    ->select(
                        "users.id",
                        "users.name",
                        DB::raw("3959 * acos(cos(radians(" . $lat . "))
                            * cos(radians(users.lat))
                            * cos(radians(users.lon) - radians(" . $lon . "))
                            + sin(radians(" . $lat . "))
                            * sin(radians(users.lat))) AS distance")
                    )
                    ->join('role_user', 'users.id', '=', 'role_user.user_id')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.id', 2)
                    ->whereNotNull('lat')
                    ->having('distance', '<', 6)
                    ->having('distance', '>', 0.0)
                    ->orderBy('distance')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                $orders = Orders::query();
                if (!empty($request->order_status)) {
                    $orders = $orders->where('order_status', '=', $request->order_status);
                    $orders = $orders
                        ->whereHas('order_items.products', function ($q) use ($users) {
                            $q->whereHas('user', function ($w) use ($users) {
                                $w->whereIn('id', $users);
                            });
                        });
                    if (\auth()->user()->vehicle_type == 'bike') {
                        $orders = $orders->whereHas('order_items.products', function ($q) {
                            return $q->where('bike', 1);
                        });
                    }
                }
                $orders = $orders->where('type', 'delivery')
                    ->orderByDesc('created_at')->paginate();
                $pagination = $orders->toArray();
            } else {
                $assignedOrders = $assignedOrders[0];
                $nearbyOrders = DB::table("orders")
                    ->select(
                        "orders.id",
                        DB::raw("3959 * acos(cos(radians(" . $assignedOrders->lat . "))
                        * cos(radians(orders.lat))
                        * cos(radians(orders.lon) - radians(" . $assignedOrders->lon . "))
                        + sin(radians(" . $assignedOrders->lat . "))
                        * sin(radians(orders.lat))) AS distance")
                    )
                    ->where(function ($q) {
                        $q->where('order_status', 'pending')
                            ->orWhere('order_status', 'ready');
                    })
                    ->whereNotNull('lat')
                    ->having('distance', '<', 2)
                    ->having('distance', '>', 0.0)
                    ->orderBy('distance')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                $orders = Orders::query();
                $orders = $orders->where('order_status', '=', $request->order_status)
                    ->where('driver_id', \auth()->id());
                $orders = $orders->orWhere(function ($q) use ($nearbyOrders) {
                    $q->whereIn('id', $nearbyOrders);
                    if (\auth()->user()->vehicle_type == 'bike') {
                        $q->whereHas('order_items.products', function ($query) {
                            return $query->where('bike', 1);
                        });
                    }
                });
                $orders = $orders->where('type', 'delivery')
                    ->orderByDesc('created_at')->paginate();
                $pagination = $orders->toArray();
            }
        } elseif ($request->has('order_status') && $request->order_status == 'complete') {
            $orders = Orders::query();
            $orders = $orders->where('type', '=', 'delivery')
                ->where('order_status', 'complete')
                ->whereNotNull('driver_id')
                ->orderByDesc('created_at')
                ->paginate();
            $pagination = $orders->toArray();
        } else {
            $orders = Orders::query();
            $orders = $orders->where('type', '=', 'delivery')
                ->where('order_status', 'ready')
                ->where('driver_id', NULL)
                ->orderByDesc('created_at')
                ->paginate();
            $pagination = $orders->toArray();
        }
        if (!$orders->isEmpty()) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->getOrderDetails($order->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * List all (assigned,complete,pending_approval,cancelled) orders
     * for a specific delivery boy
     * @author Huzaifa Haleem
     */
    public function driverOrders(Request $request, $driver_id)
    {
        /* delivery_status:assigned,complete,pending_approval,cancelled */
        $orders = Orders::select('id')->where('driver_id', '=', $driver_id)
            ->where('delivery_status', '=', $request->delivery_status)
            ->where('type', '=', 'delivery')
            ->paginate(10);
        $pagination = $orders->toArray();
        if (!$orders->isEmpty()) {
            $order_data = [];
            foreach ($orders as $order) {
                $order_data[] = $this->getOrderDetails($order->id);
            }
            unset($pagination['data']);
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => '',
                'pagination' => $pagination
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * Assigns an order to a specific delivery boy
     * @author Huzaifa Haleem
     */
    public function assignOrder(Request $request)
    {
        $order = Orders::find($request->order_id);
        if ($order) {
            $order->delivery_status = $request->delivery_status;
            $order->driver_id = $request->driver_id;
            $order->order_status = $request->order_status;
            $order->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_ASSIGNED')
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * This API is consumed on two occasions
     * 1) When the driver is "ACCEPTING" the order
     * 2) When the driver is "COMPLETING" the order
     * @author Huzaifa Haleem
     * @version 1.1.1
     */
    public function updateAssign(Request $request)
    {
        $order = Orders::find($request->order_id);
        if ($order) {
            $order->delivery_status = $request->delivery_status;
            $order->driver_id = $request->driver_id;
            $order->order_status = $request->order_status;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_UPDATED')
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * A delivery boy can cancel a specific order through this function
     * @author Muhammad Abdullah Mirza
     */
    public function cancelOrder(Request $request)
    {
        $order = Orders::find($request->order_id);
        if ($order) {
            $order->driver_id = NULL;
            $order->order_status = "ready";
            $order->delivery_status = "cancelled";
            $order->save();
            return response()->json([
                'data' => [],
                'status' => true,
                'message' => config('constants.ORDER_CANCELLED')
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * Cancel's a customer order
     * @author Muhammad Abdullah Mirza
     */
    public function customerCancelOrder(Request $request)
    {
        $order = Orders::find($request->order_id);
        $product_ids = explode(',', $request->product_ids);
        $count = 0;
        print_r($product_ids);
        exit;
        if (!is_null($order)) {
            /**
             * Order cenceled by user & not accepted by store then full refund
             */
            if ($order->order_status == "pending") {
                $order->order_status = 'cancelled';
                $order->save();
                // foreach ($product_ids as $product_id) {
                //     $count++;
                // }
                return response()->json([
                    'data' => $order,
                    'status' => true,
                    'message' => config('constants.ORDER_CANCELLED')
                ], 200);
            }
            /**
             * Order cenceled by user & accepted by store but not picked by the driver then deduct handling charges
             */
            else if ($order->order_status == "accepted" || $order->order_status == "ready") {
                $order->order_status = 'cancelled';
                $order->save();
                // foreach ($product_ids as $product_id) {
                //     $count++;
                // }
                return response()->json([
                    'data' => $order,
                    'status' => true,
                    'message' => config('constants.ORDER_CANCELLED')
                ], 200);
            }
            /**
             * Order cenceled by user, accepted by store & picked by the driver then multiply driver's fee by 2 plus add handling charge & service fee
             */
            else if ($order->order_status == "onTheWay") {
                $order->order_status = 'cancelled';
                $order->save();
                // foreach ($product_ids as $product_id) {
                //     $count++;
                // }
                return response()->json([
                    'data' => $order,
                    'status' => true,
                    'message' => config('constants.ORDER_CANCELLED')
                ], 200);
            }
        } else {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => config('constants.NO_RECORD')
            ], 200);
        }
    }
    /**
     * Update's the order
     * @author Muhammad Abdullah Mirza
     */
    public function updateOrder(Request $request)
    {
        $order_ids = $request->ids;
        $order_arr = explode(',', $order_ids);
        $count = 0;
        foreach ($order_arr as $order_id) {
            $order = Orders::find($order_id);
            if ($request->payment_status == "paid" && $order->payment_status != "paid" && $request->order_status == 'complete' && $order->order_status != 'complete' && $request->delivery_status == 'delivered' && $order->delivery_status != 'delivered') {
                $user = User::find($order->seller_id);
                $user_money = $user->pending_withdraw;
                $user->pending_withdraw = $order->order_total + $user_money;
                $user->save();
                //$this->calculateDriverFair($order, $user);
            }
            $order->lat = $request->lat;
            $order->lon = $request->lon;
            $order->type = $request->type;
            if ($request->type == 'delivery') {
                $order->receiver_name = $request->receiver_name;
                $order->phone_number = $request->phone_number;
                $order->address = $request->address;
                $order->house_no = $request->house_no;
                $order->flat = $request->flat;
                $order->delivery_charges = $request->delivery_charges;
                $order->service_charges = $request->service_charges;
            }
            $order->description = $request->description;
            $order->payment_status = $request->payment_status;
            $order->order_status = $request->order_status;
            $order->transaction_id = $request->transaction_id;
            $order->driver_charges = $request->driver_charges;
            $order->driver_traveled_km = $request->driver_traveled_km;
            $order->save();
            $count++;
        }
        return response()->json([
            'data' => $this->getOrdersFromIds($order_arr),
            'status' => true,
            'message' => 'Order Added Successfully'
        ], 200);
    }
    /**
     * It is used to fetch the information of multiple orders w.r.t their ID's
     * @author Huzaif Haleem
     */
    public function getOrdersFromIds(array $ids)
    {
        $orders = [];
        foreach ($ids as $orderId) $orders[] = $this->getOrderDetails($orderId);

        return $orders;
    }
    /**
     * It is used to fetch the information of a single order w.r.t it's ID
     * @author Huzaifa Haleem
     */
    public function getOrderDetails(int $orderId)
    {
        $temp = [];
        $order = Orders::find($orderId);
        $temp['order'] = $order;
        $temp['order_items'] = OrderItems::with('products.store')->where('order_id', '=', $orderId)->get();

        return $temp;
    }
    /**
     * It will get order details via given id
     */
    public function getOrderDetailsTwo(Request $request)
    {
        try {
            $validated_data = Validator::make($request->route()->parameters(), [
                'id' => 'required|integer'
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->error());
            }
            if (!Orders::checkIfOrderExists($request->id)) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
            $order = Orders::with(['customer', 'store', 'order_items', 'order_items.products'])
                ->where('id', $request->id)->first();
            return JsonResponseServices::getApiResponse(
                $order,
                config('constants.TRUE_STATUS'),
                "",
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will store the estimated time
     * Of an order provided via id
     */
    public function storeEstimatedTime($id)
    {
        $order = Orders::findOrFail($id);
        $order->estimated_time = request()->estimated_time;
        $order->save();
        return $order->toArray();
    }
    /**
     * It will calculate the total distance between client & store location & then
     * It will return the total distance in Miles
     * @author Muhammad Abdullah Mirza
     */
    // public function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    // {
    //     $long1 = deg2rad($longitudeFrom);
    //     $long2 = deg2rad($longitudeTo);
    //     $lat1 = deg2rad($latitudeFrom);
    //     $lat2 = deg2rad($latitudeTo);

    //     //Haversine Formula
    //     $dlong = $long2 - $long1;
    //     $dlati = $lat2 - $lat1;
    //     $val = pow(sin($dlati / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlong / 2), 2);
    //     $res = 2 * asin(sqrt($val));

    //     //Radius of Earth in Miles
    //     $radius = 3958.8;

    //     //$miles = round($res*$radius);
    //     $miles = $res * $radius;

    //     return ($miles);
    // }
}
