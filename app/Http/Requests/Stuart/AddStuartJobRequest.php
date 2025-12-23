<?php

namespace App\Http\Requests\Stuart;

use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStuartJobRequest extends FormRequest
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
            'senderEmail' => 'required|email',
            'pickupAddress' => 'required|string',
            'dropoffAddress' => 'required|string',
            'unitAddress' => 'required|string',
            'productDetails' => 'nullable|string',
            'packageTransportType' => ['required', Rule::enum(PackageTransportTypeEnum::class)],
            'packageWeight' => ['required', Rule::enum(PackageWeightEnum::class)],
        ];
    }
}
