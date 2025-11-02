<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => [
                'required',
                'email',
                'unique:admins,email',
                Rule::unique('clients', 'email')->ignore($this->route('admin')) // Email unique entre clients et admins
            ],
            'telephone' => [
                'required',
                'string',
                'unique:admins,telephone',
                Rule::unique('clients', 'telephone')->ignore($this->route('admin')), // Téléphone unique entre clients et admins
                new \App\Rules\SenegalPhoneRule,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'Veuillez saisir un email valide.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }
}
