<?php

namespace App\Http\Requests\VanProduct;

use Illuminate\Foundation\Http\FormRequest;

class ListVanProductByIdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * @return bool
     */
    public function authorize()
    {
        // Allow all requests. Custom authorization logic can be added here.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * The request expects a 'van_id' in the POST payload.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'van_id' is required, must be an integer, and must exist in the 'vans' table
            'van_id' => 'required|integer|exists:vans,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * 
     * These messages will be returned in a standardized API response.
     *
     * @return array
     */
    public function messages()
    {
        
    }
}