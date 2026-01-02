<?php

namespace App\Http\Requests\RequestedDelivery;

use App\Policies\RequestedDeliveryPolicy;
use App\Rules\Buyer\BuyerId;
use Illuminate\Foundation\Http\FormRequest;

class ListRequestedDeliveryRequest extends FormRequest
{
    function __construct(protected RequestedDeliveryPolicy $requestedDeliveryPolicy) {}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->requestedDeliveryPolicy->viewAny((int) $this['creatorId']);

        // return $this->requestedDeliveryPolicy->viewAny($this->route('creatorId'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'creatorId' => $this['creatorId'],
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
            'creatorId' => ['required', 'integer', new BuyerId],
        ];
    }
}
