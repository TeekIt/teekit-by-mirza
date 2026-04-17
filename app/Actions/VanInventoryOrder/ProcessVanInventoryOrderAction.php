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
        string $vanLocation,
        OrderTypeEnum $orderType,
        array $orderItems,
        float $orderTotal
    ): VanInventoryOrder {
        
        return DB::transaction(function () use (
            $companyId,
            $vanId,
            $vanLocation,
            $orderType,
            $orderItems,
            $orderTotal
        ): VanInventoryOrder {
            $order = VanInventoryOrder::add(
                $companyId,
                $vanId,
                $orderTotal,
                $orderType,
                $vanLocation,
            );

            foreach ($orderItems as $item) {
                $order->vanInventoryOrderItems()->create([
                    'product_id' => $item['id'],
                    'seller_id' => $item['sellerId'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            $this->sendEmailsToSellers($orderItems, $vanLocation);

            return $order;
        });
    }

    private function sendEmailsToSellers(array $orderItems, string $vanLocation): void
    {
        $orderItemsBySeller = [];
        foreach ($orderItems as $item) {
            $orderItemsBySeller[$item['sellerId']][] = $item;
        }

        foreach ($orderItemsBySeller as $sellerId => $orderItems) {
            $seller = User::getUserByID($sellerId, ['id', 'name', 'email']);

            EmailServices::sendVanInventoryOrderMail(
                $seller->email,
                $seller->name,
                $orderItems,
                $vanLocation,
            );
        }
    }
}
