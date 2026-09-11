<?php

namespace App\Http\Requests\Contributor;

use App\Enums\DataRequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isContributor() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(DataRequestType::class)],
            'contribution_id' => ['nullable', 'integer'],
            'details' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
