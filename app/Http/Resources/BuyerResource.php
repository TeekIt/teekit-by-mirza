<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'l_name' => $this->l_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'postal_code' => $this->postal_code,
            // 'business_name' => $this->business_name,
            // 'business_phone' => $this->business_phone,
            // 'business_location' => $this->business_location,
            // 'business_hours' => $this->business_hours,
            // 'bank_details' => $this->bank_details,
            // 'user_img' => $this->user_img,
            // 'pending_withdraw' => $this->pending_withdraw,
            // 'total_withdraw' => $this->total_withdraw,
            // 'is_online' => $this->is_online,
            // 'last_login' => $this->last_login,
            'roles' => [
                'buyer'
            ],
        ];
    }
}
