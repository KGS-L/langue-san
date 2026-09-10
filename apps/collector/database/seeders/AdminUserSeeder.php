<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->command?->warn('Admin non créé : renseigne SEED_ADMIN_EMAIL et SEED_ADMIN_PASSWORD dans .env.');
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
