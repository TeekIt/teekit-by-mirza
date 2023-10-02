<?php

namespace App\Http\Controllers;

use App\OrderItems;
use App\Orders;
use App\Products;
use App\Qty;
use App\User;
use App\Services\TwilioSmsService;
use App\VerificationCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class OrdersController extends Controller
{
    /**
     * List orders w.r.t Seller ID
     * @author Huzaifa Haleem
     * @version 1.1.0
     */
    public function index(Request $request)
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
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * List 2 products from recent orders of a customer
     * w.r.t Store & Customer ID
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function recentOrders(Request $request)
    {
        try {
            $recent_orders_prods_ids = DB::table('orders')
                ->select('orders.id', 'order_items.product_id')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.user_id', '=', Auth::id())
                ->where('orders.seller_id', '=', $request->store_id)
                ->orderByDesc('id')
                ->limit(2)
                ->get();
            if (!$recent_orders_prods_ids->isEmpty()) {
                $recent_orders_prods_data = [];
                foreach ($recent_orders_prods_ids as $product_id) {
                    $recent_orders_prods_data[] = Products::getProductInfo($product_id->product_id);
                }
                return response()->json([
                    'data' => $recent_orders_prods_data,
                    'status' => true,
                    'message' => ''
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 200);
        }
    }
    /**
     * List all ready or delivered orders
     * for a specific delivery boy
     * @author Huzaifa Haleem
     * @version 1.1.1
     */
    public function sellerOrders(Request $request)
    {
        $lat = \auth()->user()->lat;
        $lon = \auth()->user()->lon;
        $orders = array();
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
     * @version 1.1.1
     */
    public function deliveryBoyOrders(Request $request, $delivery_boy_id)
    {   //delivery_status:assigned,complete,pending_approval,cancelled
        $orders = Orders::query()->select('id')->where('delivery_boy_id', '=', $delivery_boy_id)
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
     * @version 1.1.0
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
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
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
     * Inserts a newly arrived order
     * @author Mirza Abdullah Izhar
     * @version 1.9.0
     */
    public function new(Request $request)
    {
        try {
            if ($request->has('type')) {
                if ($request->type == 'delivery') {
                    $validatedData = Validator::make($request->all(), [
                        'lat' => 'required',
                        'lon' => 'required',
                        'receiver_name' => 'required',
                        'phone_number' => 'required|string|min:13|max:13',
                        'address' => 'required',
                        'house_no' => 'required',
                        'delivery_charges' => 'required',
                        'service_charges' => 'required',
                        'device' => 'sometimes'
                    ]);
                    if ($validatedData->fails()) {
                        return response()->json([
                            'data' => [],
                            'status' => false,
                            'message' => $validatedData->errors()
                        ], 422);
                    }
                } elseif ($request->type == 'self-pickup') {
                    $validatedData = Validator::make($request->all(), [
                        'phone_number' => 'string|min:13|max:13'
                    ]);
                    if ($validatedData->fails()) {
                        return response()->json([
                            'data' => [],
                            'status' => false,
                            'message' => $validatedData->errors()
                        ], 422);
                    }
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => 'The type field is required.'
                ], 422);
            }
            $grouped_seller = [];
            foreach ($request->items as $item) {
                // $product_id = $item['product_id'];
                // $qty = $item['qty'];
                // $user_choice = $item['user_choice'];
                // $product_price = (new ProductsController())->getProductPrice($item['product_id']);
                // $product_seller_id = (new ProductsController())->getProductSellerID($item['product_id']);
                // $product_volumn = (new ProductsController())->getProductVolumn($item['product_id']);
                // $product_weight = (new ProductsController())->getProductWeight($item['product_id']);
                $temp = [];
                $temp['product_id'] = $item['product_id'];
                $temp['qty'] = $item['qty'];
                $temp['user_choice'] = $item['user_choice'];
                $temp['price'] = Products::getProductPrice($item['product_id']);
                $product = Products::getOnlyProductDetailsById($item['product_id']); 
                $temp['seller_id'] = $product->user_id;
                $temp['volumn'] = $product->height * $product->width * $product->length;
                $temp['weight'] = $product->weight;
                $grouped_seller[$temp['seller_id']][] = $temp;
                Qty::subtractProductQty($temp['seller_id'], $item['product_id'], $item['qty']);
            }
            $count = 0;
            $order_arr = [];
            $user_id = auth()->id();
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
                $seller = User::getUserByID($seller_id);
                /* 
                * Adding amount into seller wallet 
                */
                User::addIntoWallet($seller_id, $order_total);
                // $seller_money = $seller->pending_withdraw;
                // $seller->pending_withdraw = $order_total + $seller_money;
                // $seller->save();
                // dd($order_total);
                if ($request->type == 'delivery') {
                    $customer_lat = $request->lat;
                    $customer_lon = $request->lon;
                    $store_lat = $seller->lat;
                    $store_lon = $seller->lon;
                    $distance = $this->getDistanceBetweenPoints($customer_lat, $customer_lon, $store_lat, $store_lon);
                    // $distance = $this->calculateDistance($customer_lat, $customer_lon, $store_lat, $store_lon);
                    $driver_charges = $this->calculateDriverFair2($total_weight, $total_volumn, $distance);
                }
                $new_order = new Orders();
                $new_order->user_id = $user_id;
                $new_order->order_total = $order_total;
                $new_order->total_items = $total_items;
                $new_order->lat = ($request->type == 'delivery') ? $customer_lat : NULL;
                $new_order->lon = ($request->type == 'delivery') ? $customer_lon : NULL;
                $new_order->type = $request->type;
                if ($request->type == 'delivery') {
                    $new_order->receiver_name = $request->receiver_name;
                    $new_order->phone_number = $request->phone_number;
                    $new_order->address = $request->address;
                    $new_order->house_no = $request->house_no;
                    $new_order->flat = $request->flat;
                    $new_order->driver_charges = $driver_charges;
                    $new_order->delivery_charges = $request->delivery_charges;
                    $new_order->service_charges = $request->service_charges;
                }
                $new_order->description = $request->description;
                $new_order->payment_status = $request->payment_status ?? "hidden";
                $new_order->seller_id = $seller_id;
                $new_order->device = $request->device ?? NULL;
                $new_order->offloading = $request->offloading ?? NULL;
                $new_order->offloading_charges = $request->offloading_charges ?? NULL;
                $new_order->save();
                $order_id = $new_order->id;
                if ($request->type == 'delivery') {
                    $verification_code = '';
                    while (strlen($verification_code) < 6) {
                        $rand_number = rand(0, time());
                        $verification_code = $verification_code . substr($rand_number, 0, 1);
                    }
                    if (url()->current() == 'https://app.teekit.co.uk/api/orders/new' || url()->current() == 'https://teekitapi.com/api/orders/new') {
                        // For sending SMS notification for "New Order"
                        $sms = new TwilioSmsService();
                        $message_for_admin = "A new order #" . $order_id . " has been received. Please check TeekIt's platform, or SignIn here now:https://app.teekit.co.uk/login";
                        $message_for_customer = "Thanks for your order. Your order has been accepted by the store. Please quote verification code: " . $verification_code . " on delivery. TeekIt";

                        $sms->sendSms($request->phone_number, $message_for_customer);
                        // $sms->sendSms('+923362451199', $message_for_customer); //Rameesha Number
                        // $sms->sendSms('+923002986281', $message_for_customer); //Fahad Number

                        // To restrict "New Order" SMS notifications only for UK numbers
                        if (strlen($seller->business_phone) == 13 && str_contains($seller->business_phone, '+44')) {
                            $sms->sendSms($seller->business_phone, $message_for_admin);
                        }
                        $sms->sendSms('+447976621849', $message_for_admin); //Azim Number
                        $sms->sendSms('+447490020063', $message_for_admin); //Eesa Number
                        $sms->sendSms('+447817332090', $message_for_admin); //Junaid Number
                        $sms->sendSms('+923170155625', $message_for_admin); //Mirza Number
                    }
                    $verification_codes = new VerificationCodes();
                    $verification_codes->order_id = $order_id;
                    $verification_codes->code = '{"code": "' . $verification_code . '", "driver_failed_to_enter_code": "NULL"}';
                    $verification_codes->save();
                }
                $order_arr[] = $order_id;
                foreach ($order as $order_item) {
                    $new_order_item = new OrderItems();
                    $new_order_item->order_id = $order_id;
                    $new_order_item->product_id = $order_item['product_id'];
                    $new_order_item->product_price = $order_item['price'];
                    $new_order_item->product_qty = $order_item['qty'];
                    $new_order_item->user_choice = $order_item['user_choice'];
                    $new_order_item->save();
                }
                $count++;
            }
            if($request->wallet_flag == 1) User::deductFromWallet($user_id, $request->wallet_deduction_amount);
            return response()->json([
                'data' => $this->getOrdersFromIds($order_arr),
                'status' => true,
                'message' => 'Order added successfully.'
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * Cancel's a customer order
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
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
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
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
        $return_data = $this->getOrdersFromIds($order_arr);
        return response()->json([
            'data' => $return_data,
            'status' => true,
            'message' => 'Order Added Successfully'
        ], 200);
    }
    /**
     * It is used to fetch the information of multiple orders w.r.t their ID's
     * @author Huzaif Haleem
     * @version 1.0.0
     */
    public function getOrdersFromIds($ids)
    {
        $raw_data = [];
        foreach ($ids as $order_id) {
            $raw_data[] = $this->getOrderDetails($order_id);
        }
        return $raw_data;
    }
    /**
     * It is used to fetch the information of a single order w.r.t it's ID
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    public function getOrderDetails($order_id)
    {
        $temp = [];
        $order = Orders::find($order_id);
        $temp['order'] = $order;
        $temp['order_items'] = OrderItems::query()->with('products.user')->where('order_id', '=', $order_id)->get();
        return $temp;
    }
    /**
     * It will get order details via
     * given id
     * @version 1.0.0
     */
    public function getOrderDetailsTwo(Request $request)
    {
        try {
            $validate = Validator::make($request->route()->parameters(), [
                'id' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validate->errors()
                ], 422);
            }
            if (!Orders::where('id', $request->id)->exists()) {
                return response()->json([
                    'date' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            }
            $order = Orders::with(['user', 'delivery_boy', 'store', 'order_items', 'order_items.products'])
                ->where('id', $request->id)->first();
            return response()->json([
                'data' => $order,
                'status' => true,
                'message' => ""
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It will store the estimated time
     * Of an order provided via id
     * @version 1.0.0
     */
    public function storeEstimatedTime($id)
    {
        $order = Orders::findOrFail($id);
        $order->estimated_time = request()->estimated_time;
        $order->save();
        return $order->toArray();
    }
    // public function getDistanceBetweenPoints($destination_lat, $destination_lon, $origin_lat, $origin_lon)
    // {
    //     $destination_address = $destination_lat . ',' . $destination_lon;
    //     $origing_address = $origin_lat . ',' . $origin_lon;
    //     /* Rameesha's URL */
    //     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";

    //     $results = json_decode(file_get_contents($url), true);
    //     $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
    //     $distanceInMiles = (float)$meters[0] * 0.000621;

    //     $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
    //     $durationInMinutes = round((int)$durationInSeconds[0] / 60);
    //     return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    // }

    // public function getDistanceBetweenPoints($latitude1, $longitude1, $latitude2, $longitude2)
    public function getDistanceBetweenPoints($destination_lat, $destination_lon, $origin_lat, $origin_lon)
    {
        // $address1 = $latitude1 . ',' . $longitude1;
        // $address2 = $latitude2 . ',' . $longitude2;

        // $url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . urlencode($address1) . "&destination=" . urlencode($address2) . "&transit_routing_preference=fewer_transfers&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";

        $destination_address = $destination_lat . ',' . $destination_lon;
        $origing_address = $origin_lat . ',' . $origin_lon;
        // Google Distance Matrix
        // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&mode=driving&key=AIzaSyBFDmGYlVksc--o1jpEXf9jVQrhwmGPxkM";

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";

        // $query = file_get_contents($url);
        // $results = json_decode($query, true);
        // $distanceString = explode(' ', $results['routes'][0]['legs'][0]['distance']['text']);

        $results = json_decode(file_get_contents($url), true);
        $meters = $results['rows'][0]['elements'][0]['distance']['value'];
        $distanceInMiles = $meters * 0.000621;

        // $miles = (int)$distanceString[0] * 0.621371;
        // return $miles > 1 ? $miles : 1;
        return (float) $distanceInMiles;
    }

    /**
     * This function will return back store open/close & product qty status
     * Along with this information it will also send store_id & product_id
     * If the store is active & product is live
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function recheckProducts(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'items' => 'required|array',
                'day' => 'required|string',
                'time' => 'required|string'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $i = 0;
            foreach ($request->items as $item) {
                $open_time = User::query()->select('business_hours->time->' . $request->day . '->open as open')
                    ->where('id', '=', $item['store_id'])
                    ->where('is_active', '=', 1)
                    ->get();

                $close_time = User::query()->select('business_hours->time->' . $request->day . '->close as close')
                    ->where('id', '=', $item['store_id'])
                    ->where('is_active', '=', 1)
                    ->get();

                $qty = Products::query()->select('qty')
                    ->where('id', '=', $item['product_id'])
                    ->where('user_id', '=', $item['store_id'])
                    ->where('status', '=', 1)
                    ->get();

                $order_data[$i]['store_id'] = $item['store_id'];
                $order_data[$i]['product_id'] = $item['product_id'];
                $order_data[$i]['closed'] = (strtotime($request->time) >= strtotime($open_time[0]->open) && strtotime($request->time) <= strtotime($close_time[0]->close)) ? "No" : "Yes";
                $order_data[$i]['qty'] = (isset($qty[0]->qty)) ? $qty[0]->qty : NULL;
                $i++;
            }
            return response()->json([
                'data' => $order_data,
                'status' => true,
                'message' => ''
            ], 200);
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * It will calculate the total distance between client & store location & then
     * It will return the total distance in Miles
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $long1 = deg2rad($longitudeFrom);
        $long2 = deg2rad($longitudeTo);
        $lat1 = deg2rad($latitudeFrom);
        $lat2 = deg2rad($latitudeTo);

        //Haversine Formula
        $dlong = $long2 - $long1;
        $dlati = $lat2 - $lat1;
        $val = pow(sin($dlati / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlong / 2), 2);
        $res = 2 * asin(sqrt($val));

        //Radius of Earth in Miles
        $radius = 3958.8;

        //$miles = round($res*$radius);
        $miles = $res * $radius;

        return ($miles);
    }
    /**
     * It will calculate the fair for a driver
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    // public function calculateDriverFair($order, $store)
    // {
    //     $childOrders = Orders::where('delivery_boy_id', $order->delivery_boy_id)
    //         ->where('id', '!=', $order->id)
    //         ->where('order_status', 'onTheWay')->get();
    //     if (count($childOrders) > 0) {
    //         foreach ($childOrders as $childOrder) {
    //             $childOrder->update(['parent_id' => $order->id]);
    //         }
    //     }
    //     $driver = User::find($order->delivery_boy_id);
    //     $driver_money = $driver->pending_withdraw;
    //     $fair_per_mile = 1.50;
    //     $pickup = 1.50;
    //     $drop_off = 1.10;
    //     $fee = 0.20;
    //     if (is_null($order->parent_id)) {
    //         $distance = $this->getDistanceBetweenPoints($order->lat, $order->lon, $store->lat, $store->lon);
    //         $totalFair = ($distance * $fair_per_mile) + $pickup + $drop_off;
    //         $teekitCharges = $totalFair * $fee;
    //         $driver->pending_withdraw = ($totalFair - $teekitCharges) + $driver_money;
    //         $driver->save();
    //         $order->driver_charges = $totalFair - $fee;
    //         $order->driver_traveled_km = (round(($distance * 1.609344), 2));
    //         $order->save();
    //     } else {
    //         $oldOrder = Orders::find($order->parent_id);
    //         $distance = $this->getDistanceBetweenPoints($order->lat, $order->lon, $oldOrder->lat, $oldOrder->lon);
    //         $pickup_val = $oldOrder->seller_id == $order->seller_id ? 0.0 : $pickup;
    //         $totalFair = ($distance * $fair_per_mile) + $drop_off + $pickup_val;
    //         $teekitCharges = $totalFair * $fee;
    //         $driver->pending_withdraw = ($totalFair - $teekitCharges) + $driver_money;
    //         $driver->save();
    //     }
    // }
    /**
     * It will calculate the fair for a driver
     * The formulas used inside this function are pre-defined by Eesa & Team
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function calculateDriverFair2($total_weight, $total_volumn, $distance)
    {
        // 38cm*38cm*38cm = 54,872cm
        if ($total_weight <= 12 || $total_volumn <= 54872) {
            // Calculate fair for Bike driver
            return round((2.6 + (1.5 * $distance)) * 0.75);
        } else {
            // Calculate fair for Car/Van driver
            return round(((2.6 + (1.75 * $distance)) + ((($total_weight - 12) / 15) * ($distance / 4))) * 0.8);
        }
    }
}
