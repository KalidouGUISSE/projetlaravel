<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|exists:comptes,id',
            'type' => 'required|in:depot,retrait,virement,frais',
            'montant' => 'required|numeric|min:0.01',
            'devise' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'dateTransaction' => 'required|date',
            'statut' => 'nullable|in:en_attente,validee,annulee',
        ];
    }
}
