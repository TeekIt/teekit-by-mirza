<?php

namespace App\Http\Requests\SuperWallPackage;

use App\Enums\OrderByEnum;
use App\Policies\SuperWallPackagePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListSuperWallPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orderBy' => ['required', Rule::enum(OrderByEnum::class)],
        ];
    }
}
