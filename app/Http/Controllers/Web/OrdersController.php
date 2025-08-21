<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\OrderItems;
use App\Orders;
use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\User;
use App\VerificationCodes;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OrdersController extends Controller
{
    /**
     * Change's order status to "delivered"
     * @author Muhammad Abdullah Mirza
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
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function markAsCompleted($order_id)
    {
        $verificationCodes = VerificationCodes::query()
            ->select('code->driver_failed_to_enter_code as driver_failed_to_enter_code')
            ->where('order_id', '=', $order_id)
            ->get();

        if (
            json_decode($verificationCodes)[0]->driver_failed_to_enter_code == "Yes" ||
            json_decode($verificationCodes)[0]->driver_failed_to_enter_code == "NULL"
        ) {
            Orders::where('id', '=', $order_id)->update([
                'order_status' => OrderStatusEnum::COMPLETE,
                'delivery_status' => DeliveryStatusEnum::COMPLETE,
            ]);

            flash('This Order Has Been Marked As Completed')->success();
        } elseif (json_decode($verificationCodes)[0]->driver_failed_to_enter_code == "No") {
            flash('This Order Is Already Marked As Completed')->success();
        }

        return redirect()->back();
    }
    /**
     * It will remove a single product from the given order
     * @version 1.0.0
     */
    public function removeProductFromOrder($order_id, $item_id, $product_price, $product_qty)
    {
        try {
            $order = Orders::find($order_id);
            $order->initial_total -= $product_price;
            $order->total_items -= $product_qty;
            $order->save();
            /* Now remove the product from order items table */
            $removed = OrderItems::where('id', '=', $item_id)->delete();
            if ($removed) {
                flash('Product Has Been Removed Successfully')->success();

                return redirect()->back();
            }
        } catch (Throwable $error) {
            report($error);

            flash('Error In Removing The Product')->error();

            return redirect()->back();
        }
    }

    /**
     * It will show the order count
     * @version 1.0.0
     */
    public function countSellerOrders()
    {
        $totalOrders = Orders::where('seller_id', '=', Auth::id())->where('payment_status', '=', 'paid')->count();
        $userSettings = User::select('settings')->where('id', '=', Auth::id())->get();

        return response()->json([
            'total_orders' => $totalOrders,
            'user_settings' => $userSettings
        ]);
    }
}
