<?php

namespace App\Actions\VanInventoryOrder;

use App\Enums\OrderTypeEnum;
use App\Models\User;
use App\Models\VanInventoryOrder;
use App\Services\EmailServices;
use Illuminate\Support\Facades\DB;

final class ProcessVanInventoryOrderAction
{
    public function execute(
        int $companyId,
        int $vanId,
        string $customerName,
        string $customerLat,
        string $customerLon,
        string $customerCountryCode,
        string $customerPhoneNumber,
        string $vanAddress,
        string $vanCountry,
        string $vanState,
        string $vanCity,
        string $vanPostcode,
        float $vanLat,
        float $vanLon,
        OrderTypeEnum $orderType,
        array $orderItems
    ): array {

        return DB::transaction(function () use (
            $companyId,
            $vanId,
            $customerName,
            $customerLat,
            $customerLon,
            $customerCountryCode,
            $customerPhoneNumber,
            $vanAddress,
            $vanCountry,
            $vanState,
            $vanCity,
            $vanPostcode,
            $vanLat,
            $vanLon,
            $orderType,
            $orderItems
        ): array {
            $groupedOrderItemsBySeller = $this->groupOrderItemsBySeller($orderItems);

            $orders = [];
            foreach ($groupedOrderItemsBySeller as $sellerId => $sellerOrderItems) {
                $sellerOrderTotal = 0.0;
                foreach ($sellerOrderItems as $item) {
                    $sellerOrderTotal += (float) $item['price'] * (int) $item['qty'];
                }

                $order = VanInventoryOrder::add(
                    companyId: $companyId,
                    sellerId: $sellerId,
                    vanId: $vanId,
                    orderTotal: $sellerOrderTotal,
                    type: $orderType,
                    customerName: $customerName,
                    customerLat: $customerLat,
                    customerLon: $customerLon,
                    countryCode: $customerCountryCode,
                    phoneNumber: $customerPhoneNumber,
                    address: $vanAddress,
                    country: $vanCountry,
                    state: $vanState,
                    city: $vanCity,
                    postcode: $vanPostcode,
                );

                foreach ($sellerOrderItems as $item) {
                    $order->orderItems()->create([
                        'product_id' => $item['id'],
                        'seller_id' => $item['sellerId'],
                        'product_price' => $item['price'],
                        'product_qty' => $item['qty'],
                    ]);
                }

                $orders[(int) $sellerId] = $order;
            }

            $this->sendEmailsToSellers($orderItems, $vanAddress);

            return $orders;
        });
    }

    private function groupOrderItemsBySeller(array $orderItems): array
    {
        $groupedOrderItemsBySeller = [];

        foreach ($orderItems as $item) {
            $groupedOrderItemsBySeller[$item['sellerId']][] = $item;
        }

        return $groupedOrderItemsBySeller;
    }

    private function sendEmailsToSellers(array $orderItems, string $vanLocation): void
    {
        $orderItemsBySeller = $this->groupOrderItemsBySeller($orderItems);

        foreach ($orderItemsBySeller as $sellerId => $orderItemsBySeller) {
            $seller = User::getUserByID($sellerId, ['id', 'name', 'email']);

            EmailServices::sendVanInventoryOrderMail(
                $seller->email,
                $seller->name,
                $orderItemsBySeller,
                $vanLocation,
            );
        }
    }
}
