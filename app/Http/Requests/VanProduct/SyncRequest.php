<?php

namespace App\Http\Requests\VanProduct;

use Illuminate\Foundation\Http\FormRequest;

class SyncRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'van_id' => 'required|exists:vans,id',
        ];
    }
}