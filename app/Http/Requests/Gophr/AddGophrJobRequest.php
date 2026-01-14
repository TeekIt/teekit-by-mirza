<?php

namespace App\Http\Requests\Gophr;

use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AddGophrJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'senderName' => 'required|string',
            'senderPhone' => 'required|numeric',
            'pickupAddress' => 'required|string',
            'dropoffAddress' => 'required|string',
            'unitAddress' => 'required|string',
            'productDetails' => 'nullable|string',
            'packageTransportType' => ['required', Rule::enum(PackageTransportTypeEnum::class)],
            'packageWeight' => ['required', Rule::enum(PackageWeightEnum::class)],
            'totalCost' => 'nullable|numeric',
            'pickupCity' => 'required|string',
            'pickupPostcode' => 'required|string|max:20',
            'pickupLat' => 'required|numeric|between:-90,90',
            'pickupLon' => 'required|numeric|between:-180,180',
            'dropOffCity' => 'required|string',
            'dropOffPostCode' => 'required|string|max:20',
            'dropoffLat' => 'required|numeric|between:-90,90',
            'dropoffLon' => 'required|numeric|between:-180,180',
        ];
    }

    public function withValidator($validator): void 
    {
        $validator->after(function ($validator): void {
            if (
                str_contains($this->route()->uri(), 'job/create') &&
                is_null($this->input('totalCost'))
            ) {
                $validator->errors()->add('totalCost', 'The total cost is required for creating a job.');

                throw new ValidationException($validator);
            }
        });
    }
}
