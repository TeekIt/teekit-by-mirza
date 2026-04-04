<?php

namespace App\Http\Requests\VanInventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreVanInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ];
    }
}