<?php

namespace App\Rules\Seller;

use App\Enums\UserRoleEnum;
use App\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsParentOrChildSellerId implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        in_array(User::getUserRole($value)->first(), [UserRoleEnum::SELLER->value, UserRoleEnum::CHILD_SELLER->value]) ?: 
        $fail('Invalid seller id, this id does not belongs to any seller');
    }
}
