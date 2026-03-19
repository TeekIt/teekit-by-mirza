<?php

namespace App\Http\Requests\Van;

use App\Models\Van;
use App\Policies\VanPolicy;
use Illuminate\Foundation\Http\FormRequest;

class ListVanRequest extends FormRequest
{
    public function __construct(public VanPolicy $vanPolicy) {}

    public function authorize(): bool
    {
        return $this->vanPolicy->view(user: null, van: Van::findOrFail($this->route('vanId')));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('vanId'),
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
            'id' => 'required|integer|exists:vans,id',
        ];
    }
}
