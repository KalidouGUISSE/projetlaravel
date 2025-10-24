<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegalPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^\+221[0-9]{9}$/', $value)) {
            $fail('Le :attribute doit être un numéro de téléphone portable Sénégalais valide (+221 suivi de 9 chiffres).');
        }
    }
}