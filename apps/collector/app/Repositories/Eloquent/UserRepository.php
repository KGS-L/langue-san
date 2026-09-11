<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['profile.locality', 'userProfile', 'roles'])
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): User
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()
            ->where('email', Str::lower(trim($email)))
            ->first();
    }

    public function create(array $data): User
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
