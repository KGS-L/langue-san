<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyContributorOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'code' => ['required', 'digits:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Indiquez votre adresse email.',
            'email.email' => 'Indiquez une adresse email valide.',
            'code.required' => 'Saisissez le code reçu par email.',
            'code.digits' => 'Le code doit contenir exactement 8 chiffres.',
        ];
    }
}
