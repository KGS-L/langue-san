<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = Str::lower(trim((string) env('SEED_ADMIN_EMAIL')));
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('Admin non créé : renseigne SEED_ADMIN_EMAIL et SEED_ADMIN_PASSWORD dans .env.');
            return;
        }

        $existingAdmin = User::query()->where('role', UserRole::ADMIN->value)->first();

        if ($existingAdmin && Str::lower($existingAdmin->email) !== $email) {
            $this->command?->warn('Un administrateur existe déjà. Le seeder ne crée jamais un second administrateur.');
            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SEED_ADMIN_NAME', 'Administrateur Langue SAN'),
                'password' => $password,
                'role' => UserRole::ADMIN,
                'status' => UserStatus::ACTIVE,
            ],
        );
    }
}
