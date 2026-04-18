<?php
namespace App\Http\Requests\VanProduct;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class ListProductsRequest extends FormRequest
{
    public function rules(): array
    {
         $vanId = auth()->guard('van')->id();

        return [
            'categoryID' => [
                'nullable', 
                'integer',
                Rule::exists('categories', 'id'),
                Rule::exists('van_products', 'category_id')->where('van_id', $vanId),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['in_stock', 'critical', 'out_of_stock']),
            ],
            'productId' => [
                'nullable',
                'integer',
                Rule::exists('van_products', 'id')->where('van_id', $vanId),
            ],
        ];
    }
}