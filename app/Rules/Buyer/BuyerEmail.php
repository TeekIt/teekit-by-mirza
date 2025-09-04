<?php

namespace App\Rules\Buyer;

use App\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BuyerEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        User::getBuyerByEmail(email: $value, columns: ['id']) ?: $fail('Invalid buyer email, this email does not belongs to any buyer');
    }
}
