<?php

namespace App\Http\Requests\VanProduct;

use Illuminate\Foundation\Http\FormRequest;

class ListProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'productId' => 'nullable|integer|exists:van_products,id',
        ];
    }

    public function messages(): array
    {
        return [
            'productId.exists' => 'The selected product does not exist.',
        ];
    }
}