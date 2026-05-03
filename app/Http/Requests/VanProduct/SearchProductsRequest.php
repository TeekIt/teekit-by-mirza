<?php
namespace App\Http\Requests\VanProduct;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class SearchProductsRequest extends FormRequest
{
    public function rules(): array
    {
         $vanId = auth()->guard('van')->id();

        return [
            'q' => [
                'required',
                'string',
                Rule::exists('van_products', 'product_name')->where('van_id', $vanId),
            ],
        ];

        
    }
}