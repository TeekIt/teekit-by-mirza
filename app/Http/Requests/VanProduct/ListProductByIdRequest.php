<?php

namespace App\Http\Requests\VanProduct;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class ListProductByIdRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // Merge route parameter into request data
        $this->merge([
            'productId' => $this->route('productId'),
        ]);
    }
    public function rules(): array
    {
        $vanId = auth()->guard('van')->id();

        return [
            'productId' => [
                'required',
                'integer',
                Rule::exists('van_products', 'id')->where('van_id', $vanId),
            ],
        ];
    }

    
}