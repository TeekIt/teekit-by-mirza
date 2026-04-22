<?php

namespace App\Http\Requests\VanProduct;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListVanProductByIdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'productId' => $this->route('productId'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * The request expects a 'van_id' in the POST payload.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'productId' => [
                'required',
                'integer',
                Rule::exists('van_products', 'id'),
            ],
        ];
    }
}
