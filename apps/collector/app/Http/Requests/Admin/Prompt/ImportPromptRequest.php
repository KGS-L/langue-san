<?php

namespace App\Http\Requests\Admin\Prompt;

use App\Models\Prompt;
use Illuminate\Foundation\Http\FormRequest;

class ImportPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Prompt::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }
}
