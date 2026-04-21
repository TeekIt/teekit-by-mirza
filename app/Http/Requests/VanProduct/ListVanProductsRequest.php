<?php

namespace App\Http\Requests\VanProduct;

use App\Enums\VanProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class ListVanProductsRequest extends FormRequest
{
    public function rules(): array
    {
        $vanId = auth()->guard('van')->id();

        return [
            'categoryId' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::enum(VanProductStatusEnum::class),
            ],
            'productId' => [
                'nullable',
                'integer',
                Rule::exists('van_products', 'id'),
            ],
        ];
    }
}
