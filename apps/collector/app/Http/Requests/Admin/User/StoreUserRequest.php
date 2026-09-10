<?php

namespace App\Http\Requests\Admin\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', User::class) ?? false; }
    public function rules(): array { return ['name' => ['required','string','max:120'], 'email' => ['required','email','max:255','unique:users,email'], 'password' => ['required','confirmed',Password::min(8)], 'role' => ['required',Rule::enum(UserRole::class)], 'status' => ['required',Rule::enum(UserStatus::class)]]; }
}
