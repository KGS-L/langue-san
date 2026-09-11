<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;

class SubmitContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'san_text' => ['nullable', 'string', 'max:5000', 'required_without:audio'],
            'audio' => [
                'nullable',
                'file',
                'max:10240',
                'mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/webm,audio/ogg,video/webm',
                'required_without:san_text',
            ],
            'audio_duration_ms' => ['nullable', 'integer', 'min:0', 'max:3600000'],
        ];
    }

    public function messages(): array
    {
        return [
            'san_text.required_without' => 'Écrivez une réponse en San ou enregistrez votre voix.',
            'audio.required_without' => 'Écrivez une réponse en San ou enregistrez votre voix.',
            'audio.max' => 'L’enregistrement audio ne doit pas dépasser 10 Mo.',
            'audio.mimetypes' => 'Le format de l’enregistrement audio n’est pas pris en charge.',
        ];
    }
}
