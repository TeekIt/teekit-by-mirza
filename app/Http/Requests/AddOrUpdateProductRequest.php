<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Enums\TransportVehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrUpdateProductRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'product_name' => 'required|string',
            'sku' => 'required|string',
            'category_id' => 'required|integer',
            'qty' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'status' => [
                'required',
                Rule::in(array_column(ProductStatus::cases(), 'value')),
            ],
            'contact' => 'required|min:10|max:10',
            'colors' => 'nullable|array',
            'feature_img' => 'required|image|max:2048',
            'gallery' => 'nullable|array',
            'vehicle' => [
                'required',
                Rule::in(array_column(TransportVehicle::cases(), 'value')),
            ],
        ];
    }
}
