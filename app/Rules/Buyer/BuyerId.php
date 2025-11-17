<?php

namespace App\Rules\Buyer;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BuyerId implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        (User::getUserRole((int) $value)->first() == UserRoleEnum::BUYER->value) ?:
        $fail('Invalid buyer id, this id does not belongs to any buyer');
    }
}
