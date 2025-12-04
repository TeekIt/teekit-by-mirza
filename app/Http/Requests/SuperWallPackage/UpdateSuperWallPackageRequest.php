<?php

namespace App\Http\Requests\SuperWallPackage;

use Illuminate\Foundation\Http\FormRequest;
use App\Policies\SuperWallPackagePolicy;

class UpdateSuperWallPackageRequest extends FormRequest
{
    public function __construct(public SuperWallPackagePolicy $superWallPackagePolicy) {}

    public function authorize(): bool
    {
        return $this->superWallPackagePolicy->update($this->user());
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'superWallPackageId' => $this['superWallPackageId'],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'superWallPackageId' => 'required|integer|exists:super_wall_packages,id',
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
