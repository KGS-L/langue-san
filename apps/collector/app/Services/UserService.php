<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function paginate(int $perPage = 20)
    {
        return $this->users->paginate($perPage);
    }

    public function createContributor(array $data): User
    {
        return DB::transaction(fn () => $this->users->create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
            'role' => UserRole::CONTRIBUTOR,
            'status' => UserStatus::ACTIVE,
        ]));
    }

    public function findOrCreatePasswordlessContributor(string $email, ?string $name = null): User
    {
        $email = Str::lower(trim($email));

        return DB::transaction(function () use ($email, $name): User {
            $user = $this->users->findByEmail($email);

            if ($user) {
                if (! $user->isContributor()) {
                    throw ValidationException::withMessages([
                        'email' => 'Cette adresse est réservée à un compte de l’équipe Langue SAN.',
                    ]);
                }

                if ($user->status !== UserStatus::ACTIVE) {
                    throw ValidationException::withMessages([
                        'email' => 'Ce compte n’est pas actif.',
                    ]);
                }

                $updates = [];
                if (! $user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                }
                if ($name && ($user->name === 'Contributeur' || blank($user->name))) {
                    $updates['name'] = $name;
                }

                return $updates ? $this->users->update($user, $updates) : $user;
            }

            $displayName = trim((string) $name);
            if ($displayName === '') {
                $localPart = Str::before($email, '@');
                $displayName = Str::of($localPart)->replace(['.', '_', '-'], ' ')->title()->toString();
                $displayName = $displayName !== '' ? $displayName : 'Contributeur';
            }

            return $this->users->create([
                'name' => $displayName,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Str::random(64),
                'role' => UserRole::CONTRIBUTOR,
                'status' => UserStatus::ACTIVE,
            ]);
        });
    }

    public function create(array $data): User
    {
        return $this->users->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (blank($data['password'] ?? null)) {
            $data = Arr::except($data, ['password']);
        }

        return $this->users->update($user, $data);
    }

    public function delete(User $user): bool
    {
        return $this->users->delete($user);
    }
}
