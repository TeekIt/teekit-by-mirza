<?php

namespace App\Services;

final class DriverFairServices
{
    /**
     * The formulas used inside the function are pre-defined by Eesa & Team
     * @author Mirza Abdullah Izhar
     */
    public static function calculateDriverFair2($total_weight, $total_volumn, $distance)
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
     /**
     * It will calculate the fair for a driver
     * @author Huzaifa Haleem
     * @version 1.0.0
     */
    // public function calculateDriverFair($order, $store)
    // {
    //     $childOrders = Orders::where('driver_id', $order->driver_id)
    //         ->where('id', '!=', $order->id)
    //         ->where('order_status', 'onTheWay')->get();
    //     if (count($childOrders) > 0) {
    //         foreach ($childOrders as $childOrder) {
    //             $childOrder->update(['parent_id' => $order->id]);
    //         }
    //     }
    //     $driver = User::find($order->driver_id);
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
}
