<?php

namespace App\Http\Requests\SuperWallPackage;

use Illuminate\Foundation\Http\FormRequest;
use App\Policies\SuperWallPackagePolicy;

class DeleteSuperWallPackageRequest extends FormRequest
{
    public function __construct(public SuperWallPackagePolicy $superWallPackagePolicy) {}

    public function authorize(): bool
    {
        return $this->superWallPackagePolicy->delete($this->user());
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
    
    public function rules(): array
    {
        return [
            'superWallPackageId' => 'required|integer|exists:super_wall_packages,id',
        ];
    }
}
