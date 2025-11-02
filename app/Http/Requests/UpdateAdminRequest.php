<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
        $adminId = $this->route('admin');

        return [
            'nom'       => 'sometimes|required|string|max:255',
            'prenom'    => 'sometimes|required|string|max:255',
            'email'     => [
                'sometimes',
                'required',
                'email',
                'unique:admins,email,' . $adminId,
                Rule::unique('clients', 'email')->ignore($adminId, 'id') // Email unique entre clients et admins
            ],
            'telephone' => [
                'sometimes',
                'required',
                'string',
                'unique:admins,telephone,' . $adminId,
                Rule::unique('clients', 'telephone')->ignore($adminId, 'id'), // Téléphone unique entre clients et admins
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