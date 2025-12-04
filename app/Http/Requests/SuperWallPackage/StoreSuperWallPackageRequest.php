<?php

namespace App\Http\Requests\SuperWallPackage;

use App\Policies\SuperWallPackagePolicy;
use Illuminate\Foundation\Http\FormRequest;

class StoreSuperWallPackageRequest extends FormRequest
{
    public function __construct(public SuperWallPackagePolicy $superWallPackagePolicy) {}

    public function authorize(): bool
    {
        return $this->superWallPackagePolicy->create($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'superFastDeliveries' => 'required|string|max:191',
            'unlimitedSuperFastDeliveriesOnOrdersAbove' => 'nullable|integer',
            'currency' => 'nullable|string|max:4',
            'minDeliveryTimeInMinutes' => 'nullable|integer|min:0',
            'guaranteeDeliveryTimeInMinutes' => 'nullable|string',
            'cashbackPercentage' => 'nullable|string',
            'vanDeliveriesDiscountPercentage' => 'nullable|string',
            'prioritySupport' => 'nullable|string',
            'usersIncluded' => 'nullable|string',
            'freeSameDayDelivery' => 'nullable|string',
        ];
    }
}
