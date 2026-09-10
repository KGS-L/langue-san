<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function paginate(int $perPage = 20) { return $this->users->paginate($perPage); }

    public function createContributor(array $data): User
    {
        return DB::transaction(fn () => $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::CONTRIBUTOR,
            'status' => UserStatus::ACTIVE,
        ]));
    }

    public function create(array $data): User { return $this->users->create($data); }

    public function update(User $user, array $data): User
    {
        if (blank($data['password'] ?? null)) $data = Arr::except($data, ['password']);
        return $this->users->update($user, $data);
    }

    public function delete(User $user): bool { return $this->users->delete($user); }
}
