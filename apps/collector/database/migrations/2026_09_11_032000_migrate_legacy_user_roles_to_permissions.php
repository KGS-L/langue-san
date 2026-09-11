<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $now = now();
        foreach (['admin', 'moderator', 'contributor'] as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now],
            );
        }

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['admin', 'moderator', 'contributor'])
            ->pluck('id', 'name');

        DB::table('users')
            ->select(['id', 'role'])
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($roleIds): void {
                foreach ($users as $user) {
                    $roleId = $roleIds[$user->role] ?? $roleIds['contributor'] ?? null;
                    if (! $roleId) {
                        continue;
                    }

                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $roleId,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $user->id,
                    ]);
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_index');
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 30)->default('contributor')->index()->after('password');
        });

        $roles = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('roles.guard_name', 'web')
            ->select(['model_has_roles.model_id', 'roles.name'])
            ->get();

        foreach ($roles as $role) {
            DB::table('users')->where('id', $role->model_id)->update(['role' => $role->name]);
        }
    }
};
