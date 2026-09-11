<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\ModeratorInvitationMail;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function paginate(int $perPage = 20)
    {
        return $this->users->paginate($perPage);
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

                $user = $updates ? $this->users->update($user, $updates) : $user;

                // Keep validator/transcriber roles if an approved project member has them.
                if (! $user->hasRole(UserRole::CONTRIBUTOR->value)) {
                    $user->assignRole(UserRole::CONTRIBUTOR->value);
                }

                return $user;
            }

            $displayName = trim((string) $name);
            if ($displayName === '') {
                $localPart = Str::before($email, '@');
                $displayName = Str::of($localPart)->replace(['.', '_', '-'], ' ')->title()->toString();
                $displayName = $displayName !== '' ? $displayName : 'Contributeur';
            }

            $user = $this->users->create([
                'name' => $displayName,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Str::random(64),
                'status' => UserStatus::ACTIVE,
            ]);
            $user->assignRole(UserRole::CONTRIBUTOR->value);

            return $user;
        });
    }

    public function inviteModerator(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => Str::lower(trim($data['email'])),
                'password' => Str::random(64),
                'status' => $data['status'] ?? UserStatus::ACTIVE,
            ]);
            $user->syncRoles([UserRole::MODERATOR->value]);

            return $user;
        });

        $token = Password::broker()->createToken($user);
        $setupUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        Mail::to($user->email)->send(new ModeratorInvitationMail($user->name, $setupUrl));

        return $user;
    }

    public function updateModerator(User $user, array $data): User
    {
        if (! $user->isModerator()) {
            throw ValidationException::withMessages([
                'user' => 'Seuls les comptes modérateurs sont modifiables depuis cet écran.',
            ]);
        }

        $updated = $this->users->update($user, Arr::only($data, ['name', 'email', 'status']));
        $updated->syncRoles([UserRole::MODERATOR->value]);

        return $updated;
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
