<?php

namespace App\Http\Requests\Admin\ProjectApplication;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectMemberAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array', 'max:2'],
            'roles.*' => [
                'string',
                'distinct',
                Rule::in([
                    UserRole::TRANSCRIBER->value,
                    UserRole::VALIDATOR->value,
                ]),
            ],
        ];
    }
}
