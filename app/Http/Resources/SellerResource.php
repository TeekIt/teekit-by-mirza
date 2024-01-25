<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
{
    private $user;
    private $distance_data;

    public function __construct($user, $distance_data = null)
    {
        $this->user = $user;
        $this->distance_data = $distance_data;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'business_name' => $this->user->business_name,
            'business_hours' => $this->user->business_hours,
            'full_address' => $this->user->full_address,
            'unit_address' => $this->user->unit_address,
            'country' => $this->user->country,
            'state' => $this->user->state,
            'city' => $this->user->city,
            'postcode' => $this->user->postcode,
            'lat' => $this->user->lat,
            'lon' => $this->user->lon,
            'user_img' => $this->user->user_img,
            'pending_withdraw' => $this->user->pending_withdraw,
            'total_withdraw' => $this->user->total_withdraw,
            'parent_store_id' => $this->user->parent_store_id,
            'is_online' => $this->user->is_online,
            'roles' => ($this->user->role_id == 2) ? ['sellers'] : ['child_sellers'],
            'distance' => (empty($this->distance_data)) ? null : $this->distance_data['distance'],
            'duration' => (empty($this->distance_data)) ? null : $this->distance_data['duration']
        ];
    }
}
