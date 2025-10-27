<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalPhoneRule;

class UpdateClientRequest extends FormRequest
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
        $clientId = $this->route('id');

        return [
            'titulaire' => 'nullable|string|max:255',
            'informationsClient' => 'nullable|array',
            'informationsClient.telephone' => [
                'nullable',
                new SenegalPhoneRule(),
                'unique:clients,telephone,' . $clientId
            ],
            'informationsClient.email' => [
                'nullable',
                'email',
                'unique:clients,email,' . $clientId
            ],
            'informationsClient.password' => 'nullable|string|min:8',
            'informationsClient.nci' => [
                'nullable',
                'string',
                'regex:/^[0-9]{13}$/'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le titulaire ne peut pas dépasser 255 caractères.',
            'informationsClient.array' => 'Les informations du client doivent être un tableau.',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.email.email' => 'L\'email doit être valide.',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'informationsClient.nci.regex' => 'Le NCI doit être composé de 13 chiffres.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Vérifier qu'au moins un champ est modifié
            $hasTitulaire = isset($data['titulaire']);
            $hasInformations = isset($data['informationsClient']) && is_array($data['informationsClient']) &&
                               (isset($data['informationsClient']['telephone']) ||
                                isset($data['informationsClient']['email']) ||
                                isset($data['informationsClient']['password']) ||
                                isset($data['informationsClient']['nci']));

            if (!$hasTitulaire && !$hasInformations) {
                $validator->errors()->add('general', 'Au moins un champ doit être modifié.');
            }
        });
    }
}
