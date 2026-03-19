<?php

namespace App\Http\Controllers\Web\v1;

use Throwable;
use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use App\Models\VerificationCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
        /**
     * Render verified orders listing view for admin
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersVerified(Request $request)
    {
        $return_arr = [];
        $verified_orders = VerificationCodes::where('code->driver_failed_to_enter_code', '=', 'No')->orderByDesc('id');
        $verified_orders = $verified_orders->paginate(10);
        $orders_p = $verified_orders;
        foreach ($verified_orders as $order) {
            $order_details = Orders::where('id', '=', $order->order_id)->first();
            $items = OrderItems::where('order_id', '=', $order->order_id)->get();
            $item_arr = [];
            foreach ($items as $item) {
                $product = Products::getProductInfoWithRelations($order_details->seller_id, $item->product_id, ['*']);
                $item['product'] = $product;
                $item_arr[] = $item;
            }
            $order['order_details'] = $order_details;
            $order['items'] = $item_arr;
            $return_arr[] = $order;
        }
        $orders = $return_arr;

        return view('admin.verified_orders', compact('orders', 'orders_p'));
    }

    /**
     * Render unverified orders listing view for admin
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersUnverified(Request $request)
    {
        $return_arr = [];
        $verified_orders = VerificationCodes::query()
            ->where('code->driver_failed_to_enter_code', '=', 'Yes')
            ->orderByDesc('id');
        $verified_orders = $verified_orders->paginate(10);
        $orders_p = $verified_orders;
        foreach ($verified_orders as $order) {
            $order_details = Orders::query()->where('id', '=', $order->order_id)->first();
            $items = OrderItems::query()->where('order_id', '=', $order->order_id)->get();
            $item_arr = [];
            foreach ($items as $item) {
                $product = Products::getProductInfoWithRelations($order_details->seller_id, $item->product_id, ['*']);
                $item['product'] = $product;
                $item_arr[] = $item;
            }
            $order['order_details'] = $order_details;
            $order['items'] = $item_arr;
            $return_arr[] = $order;
        }
        $orders = $return_arr;

        return view('admin.unverified_orders', compact('orders', 'orders_p'));
    }

    /**
     * Delete selected orders
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function adminOrdersDel(Request $request)
    {
        for ($i = 0; $i < count($request->orders); $i++) {
            DB::table('orders')->where('id', '=', $request->orders[$i])->delete();
            DB::table('order_items')->where('order_id', '=', $request->orders[$i])->delete();
            DB::table('verification_codes')->where('order_id', '=', $request->orders[$i])->delete();
        }

        return response('Orders Deleted Successfully');
    }

    /**
     * It will show complete orders
     * based on the given criteria
     *
     * @version 1.0.0
     */
    public function completeOrders()
    {
        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.created_by_id', '=', 'users.id')
            ->leftJoin('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->where('created_by_type', (new User)->getMorphClass())
            ->where('delivery_status', '=', DeliveryStatusEnum::COMPLETE)
            ->where('order_status', '=', OrderStatusEnum::COMPLETE)
            ->select(
                'drivers.f_name',
                'drivers.l_name',
                'orders.id',
                'orders.total_items',
                'orders.phone_number',
                'orders.house_no',
                'orders.address',
                'orders.type',
                'users.name'
            )
            ->paginate(10);

        return view('admin.complete-orders', compact('orders'));
    }

    /**
     * Change's order status to "delivered"
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.0.0
     */
    public function markAsDelivered($order_id)
    {
        Orders::where('id', '=', $order_id)->update(['order_status' => 'delivered']);

        flash('This Order Has Been Marked As Delivered')->success();

        return redirect()->back();
    }

    /**
     * It change's the order_status & delivery_status to "complete"
     * Only if the driver is failed to enter the correct verification code
     *
     * @author Muhammad Abdullah Mirza
     *
     * @version 1.1.0
     */
    public function markAsCompleted($order_id)
    {
        $verificationCodes = VerificationCodes::query()
            ->select('code->driver_failed_to_enter_code as driver_failed_to_enter_code')
            ->where('order_id', '=', $order_id)
            ->get();

        if (
            json_decode($verificationCodes)[0]->driver_failed_to_enter_code == 'Yes' ||
            json_decode($verificationCodes)[0]->driver_failed_to_enter_code == 'NULL'
        ) {
            Orders::where('id', '=', $order_id)->update([
                'order_status' => OrderStatusEnum::COMPLETE,
                'delivery_status' => DeliveryStatusEnum::COMPLETE,
            ]);

            flash('This Order Has Been Marked As Completed')->success();
        } elseif (json_decode($verificationCodes)[0]->driver_failed_to_enter_code == 'No') {
            flash('This Order Is Already Marked As Completed')->success();
        }

        return redirect()->back();
    }

    /**
     * It will remove a single product from the given order
     *
     * @version 1.0.0
     */
    // public function removeProductFromOrder($order_id, $item_id, $product_price, $product_qty)
    // {
    //     try {
    //         $order = Orders::find($order_id);
    //         $order->initial_total -= $product_price;
    //         $order->total_items -= $product_qty;
    //         $order->save();
    //         /* Now remove the product from order items table */
    //         $removed = OrderItems::where('id', '=', $item_id)->delete();
    //         if ($removed) {
    //             flash('Product Has Been Removed Successfully')->success();

    //             return redirect()->back();
    //         }
    //     } catch (Throwable $error) {
    //         report($error);

    //         flash('Error In Removing The Product')->error();

    //         return redirect()->back();
    //     }
    // }

    /**
     * It will show the order count
     *
     * @version 1.0.0
     */
    public function countSellerOrders()
    {
        $totalOrders = Orders::where('seller_id', '=', Auth::id())->where('payment_status', '=', 'paid')->count();
        $userSettings = User::select('settings')->where('id', '=', Auth::id())->get();

        return response()->json([
            'total_orders' => $totalOrders,
            'user_settings' => $userSettings,
        ]);
    }
}
