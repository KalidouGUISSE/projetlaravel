<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SenegalNciRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[0-9]{13}$/', $value)) {
            $fail('Le :attribute doit être un numéro de Carte d\'Identité Sénégalais valide (13 chiffres).');
        }
    }
}