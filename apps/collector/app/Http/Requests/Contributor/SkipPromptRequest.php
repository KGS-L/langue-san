<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;

class SkipPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
