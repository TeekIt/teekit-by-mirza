<?php

namespace App\Http\Controllers;

use App\OrderItems;
use App\Orders;
use App\Products;
use App\Qty;
use App\Services\DriverFairServices;
use App\Services\GoogleMapServices;
use App\Services\JsonResponseServices;
use App\User;
use App\Services\TwilioSmsService;
use App\Services\VerificationCodeServices;
use App\VerificationCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Inserts a newly arrived order
     * @author Muhammad Abdullah Mirza
     */
    public function new(Request $request)
    {
        try {
            if ($request->has('type')) {
                if ($request->type == 'delivery') {
                    $rules = [
                        'type' => 'required|string',
                        'items' => 'required|array',
                        'lat' => 'required|numeric|between:-90,90',
                        'lon' => 'required|numeric|between:-180,180',
                        'receiver_name' => 'required|regex:/^[A-Za-z\s]+$/',
                        'phone_number' => 'required|string|min:13|max:13',
                        'address' => 'required|string',
                        'house_no' => 'required|string',
                        'delivery_charges' => 'required|numeric',
                        'service_charges' => 'required|numeric',
                        'device' => 'sometimes',
                        // 'seller_id' => 'required|integer'
                    ];
                } elseif ($request->type == 'self-pickup') {
                    $rules = [
                        'type' => 'required|string',
                        'phone_number' => 'string|min:13|max:13',
                        // 'seller_id' => 'required|integer'
                    ];
                }
            } else {
                return JsonResponseServices::getApiValidationFailedResponse(json_decode('{"type": ["The type field is required."]}'));
            }

            $validated_data = Validator::make($request->all(), $rules);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }
            $grouped_seller = [];
            foreach ($request->items as $item) {
                $temp = [];
                $temp['product_id'] = $item['product_id'];
                $temp['qty'] = $item['qty'];
                $temp['user_choice'] = $item['user_choice'];
                $temp['price'] = Products::getProductPrice($item['product_id']);
                $product = Products::getOnlyProductDetailsById($item['product_id']);
                $temp['seller_id'] = $product->user_id;
                // $temp['seller_id'] = $request->seller_id;
                $temp['volumn'] = $product->height * $product->width * $product->length;
                $temp['weight'] = $product->weight;
                $grouped_seller[$temp['seller_id']][] = $temp;
                Qty::subtractProductQty($temp['seller_id'], $item['product_id'], $item['qty']);
            }
            $count = 0;
            $order_arr = [];
            $customer_id = auth()->id();
            foreach ($grouped_seller as $seller_id => $order) {
                $total_weight = 0;
                $total_volumn = 0;
                $order_total = 0;
                $total_items = 0;
                foreach ($order as $order_item) {
                    $total_weight = $total_weight + $order_item['weight'];
                    $total_volumn = $total_volumn + $order_item['volumn'];
                    $total_items = $total_items + $order_item['qty'];
                    $order_total = $order_total + ($order_item['price'] * $order_item['qty']);
                }
                $seller = User::getUserByID($seller_id, ['business_phone', 'lat', 'lon']);
                /*
                * Adding amount into seller wallet
                */
                User::addIntoWallet($seller_id, $order_total);
                $new_order = new Orders();
                $new_order->user_id = $customer_id;
                $new_order->order_total = $order_total;
                $new_order->total_items = $total_items;
                if ($request->type == 'delivery') {
                    $customer_lat = $request->lat;
                    $customer_lon = $request->lon;
                    $store_lat = $seller->lat;
                    $store_lon = $seller->lon;
                    // $distance = $this->calculateDistance($customer_lat, $customer_lon, $store_lat, $store_lon);
                    $distance = GoogleMapServices::getDistanceInMiles($store_lat, $store_lon, $customer_lat, $customer_lon);
                    $driver_charges = DriverFairServices::calculateDriverFair2($total_weight, $total_volumn, $distance);
                    $new_order->lat = $customer_lat;
                    $new_order->lon = $customer_lon;
                    $new_order->receiver_name = $request->receiver_name;
                    $new_order->phone_number = $request->phone_number;
                    $new_order->address = $request->address;
                    $new_order->house_no = $request->house_no;
                    $new_order->flat = $request->flat;
                    $new_order->driver_charges = $driver_charges;
                    $new_order->delivery_charges = $request->delivery_charges;
                    $new_order->service_charges = $request->service_charges;
                }
                $new_order->type = $request->type;
                $new_order->description = $request->description;
                $new_order->payment_status = $request->payment_status ?? "hidden";
                $new_order->seller_id = $seller_id;
                $new_order->device = $request->device ?? NULL;
                $new_order->offloading = $request->offloading ?? NULL;
                $new_order->offloading_charges = $request->offloading_charges ?? NULL;
                $new_order->save();
                $order_id = $new_order->id;
                if ($request->type == 'delivery') {
                    $verification_code = VerificationCodeServices::generateCode();
                    if (url()->current() == config('constants.LIVE_DASHBOARD_URL') . '/api/orders/new' || url()->current() == config('constants.APIS_DOMAIN_URL') . '/api/orders/new') {
                        // Msg for sending SMS notification of this "New Order"
                        $message_for_admin = "A new order #" . $order_id . " has been received. Please check TeekIt's platform, or SignIn here now:https://app.teekit.co.uk/login";
                        $message_for_customer = "Thanks for your order. Your order has been accepted by the store. Please quote verification code: " . $verification_code . " on delivery. TeekIt";

                        TwilioSmsService::sendSms($request->phone_number, $message_for_customer);
                        // TwilioSmsService::sendSms('+923362451199', $message_for_customer); //Rameesha Number
                        // TwilioSmsService::sendSms('+923002986281', $message_for_customer); //Fahad Number

                        // To restrict "New Order" SMS notifications only for UK numbers
                        if (strlen($seller->business_phone) == 13 && str_contains($seller->business_phone, '+44')) {
                            TwilioSmsService::sendSms($seller->business_phone, $message_for_admin);
                        }
                        TwilioSmsService::sendSms('+447976621849', $message_for_admin); //Azim Number
                        TwilioSmsService::sendSms('+447490020063', $message_for_admin); //Eesa Number
                        TwilioSmsService::sendSms('+447817332090', $message_for_admin); //Junaid Number
                        TwilioSmsService::sendSms('+923170155625', $message_for_admin); //Mirza Number
                    }
                    VerificationCodes::insertVerificationCode($order_id, $verification_code);
                }
                $order_arr[] = $order_id;
                foreach ($order as $order_item) {
                    OrderItems::insertInfo($order_id, $order_item['product_id'], $order_item['price'], $order_item['qty'], $order_item['user_choice']);
                }
                $count++;
            }
            if ($request->wallet_flag == 1) User::deductFromWallet($customer_id, $request->wallet_deduction_amount);
            return JsonResponseServices::getApiResponse(
                $this->getOrdersFromIds($order_arr),
                config('constants.TRUE_STATUS'),
                config('constants.ORDER_PLACED_SUCCESSFULLY'),
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
     * List orders w.r.t Seller ID
     * @author Huzaifa Haleem
     */
    public function showLoggedinBuyerOrders(Request $request)
    {
        try {
            $orders = Orders::select('id')->where('user_id', '=', Auth::id())->orderByDesc('id');
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
            $validated_data = Validator::make($request->all(), [
                'prducts_limit' => 'required|integer',
                'seller_id' => 'required|integer'
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->error());
            }
            $order = Orders::getRecentOrderByBuyerId(Auth::id(), $request->prducts_limit, $request->seller_id);
            if (!empty($order)) {
                $recent_order_prods_data = [];
                foreach ($order->products as $product) $recent_order_prods_data[] = Products::getProductInfo($request->seller_id, $product->id, ['*']);
                return JsonResponseServices::getApiResponse(
                    $recent_order_prods_data,
                    true,
                    '',
                    config('constants.HTTP_OK')
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
            $assignedOrders = Orders::where('delivery_boy_id', \auth()->id())->where('delivery_status', 'assigned')->get();
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
                    ->where('delivery_boy_id', \auth()->id());
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
                ->whereNotNull('delivery_boy_id')
                ->orderByDesc('created_at')
                ->paginate();
            $pagination = $orders->toArray();
        } else {
            $orders = Orders::query();
            $orders = $orders->where('type', '=', 'delivery')
                ->where('order_status', 'ready')
                ->where('delivery_boy_id', NULL)
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
    public function deliveryBoyOrders(Request $request, $delivery_boy_id)
    {
        //delivery_status:assigned,complete,pending_approval,cancelled
        $orders = Orders::select('id')->where('delivery_boy_id', '=', $delivery_boy_id)
            ->where('delivery_status', '=', $request->delivery_status)
            ->where('type', '=', 'delivery')
            ->paginate();
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
            $order->delivery_boy_id = $request->delivery_boy_id;
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
            $order->delivery_boy_id = $request->delivery_boy_id;
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
            $order->delivery_boy_id = NULL;
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
    public function getOrdersFromIds($ids)
    {
        $raw_data = [];
        foreach ($ids as $order_id) $raw_data[] = $this->getOrderDetails($order_id);
        return $raw_data;
    }
    /**
     * It is used to fetch the information of a single order w.r.t it's ID
     * @author Huzaifa Haleem
     */
    public function getOrderDetails($order_id)
    {
        $temp = [];
        $order = Orders::find($order_id);
        $temp['order'] = $order;
        $temp['order_items'] = OrderItems::with('products.store')->where('order_id', '=', $order_id)->get();
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
            $order = Orders::with(['user', 'delivery_boy', 'store', 'order_items', 'order_items.products'])
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
