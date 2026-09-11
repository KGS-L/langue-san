<?php

namespace App\Http\Requests\Contributor;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isContributor() ?? false;
    }

    public function rules(): array
    {
        return [
            'public_profile_enabled' => ['required', 'boolean'],
            'public_display_name' => ['nullable', 'string', 'max:120'],
            'public_bio' => ['nullable', 'string', 'max:500'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
