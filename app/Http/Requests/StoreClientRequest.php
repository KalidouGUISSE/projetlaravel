<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalNciRule;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // true si tout utilisateur peut créer un client
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
                'unique:clients,email',
                \Illuminate\Validation\Rule::unique('admins', 'email')->ignore($this->route('client')) // Email unique entre clients et admins
            ],
            'telephone' => [
                'required',
                'string',
                'min:9',
                'max:15',
                'unique:clients,telephone',
                \Illuminate\Validation\Rule::unique('admins', 'telephone')->ignore($this->route('client')) // Téléphone unique entre clients et admins
            ],
            'adresse'   => 'nullable|string|max:255',
            'nci'       => ['required', new SenegalNciRule()],
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
        ];
    }
}
