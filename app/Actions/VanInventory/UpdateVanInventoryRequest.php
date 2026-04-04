<?php

namespace App\Http\Requests\VanInventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVanInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_name' => 'sometimes|required|string|max:255',
            'qty'          => 'sometimes|required|integer|min:0',
            'price'        => 'sometimes|required|numeric|min:0',
        ];
    }
}