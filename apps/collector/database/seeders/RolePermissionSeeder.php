<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access admin',
            'manage moderators',
            'manage reference data',
            'manage prompts',
            'view contributions',
            'transcribe contributions',
            'validate contributions',
            'export dataset',
            'review project applications',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate(UserRole::ADMIN->value, 'web');
        $moderator = Role::findOrCreate(UserRole::MODERATOR->value, 'web');
        $contributor = Role::findOrCreate(UserRole::CONTRIBUTOR->value, 'web');
        $transcriber = Role::findOrCreate(UserRole::TRANSCRIBER->value, 'web');
        $validator = Role::findOrCreate(UserRole::VALIDATOR->value, 'web');

        $admin->syncPermissions($permissions);

        $moderator->syncPermissions([
            'access admin',
            'manage reference data',
            'manage prompts',
            'view contributions',
            'transcribe contributions',
            'validate contributions',
            'review project applications',
        ]);

        $transcriber->syncPermissions([
            'access admin',
            'view contributions',
            'transcribe contributions',
        ]);

        $validator->syncPermissions([
            'access admin',
            'view contributions',
            'validate contributions',
        ]);

        // A contributor account has no back-office permission by default.
        $contributor->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
