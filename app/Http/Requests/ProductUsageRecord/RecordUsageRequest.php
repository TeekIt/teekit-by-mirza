<?php

namespace App\Http\Requests\ProductUsageRecord;

use Illuminate\Foundation\Http\FormRequest;

class RecordUsageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vanId'         => 'required|integer|exists:vans,id',
            'productId'     => 'required|integer|exists:van_products,id',
            'quantityUsed'  => 'required|integer|min:1',
            'jobReference'  => 'required|string|max:50',
            'timestamp'     => 'nullable|date',
        ];
    }
}