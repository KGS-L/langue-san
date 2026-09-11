<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_profiles')) {
            return;
        }

        if (! Schema::hasColumn('user_profiles', 'country_code')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('country_code', 2)->nullable()->index();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'age_range')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('age_range', 30)->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'profession')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('profession', 80)->nullable()->index();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'profession_other')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('profession_other', 150)->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'organization')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('organization', 180)->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'avatar_path')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('avatar_path')->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'public_profile_enabled')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->boolean('public_profile_enabled')->default(false)->index();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'public_display_name')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('public_display_name', 120)->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'public_bio')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->text('public_bio')->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'github_url')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('github_url')->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'linkedin_url')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->string('linkedin_url')->nullable();
            });
        }

        if (! Schema::hasColumn('user_profiles', 'onboarding_completed_at')) {
            Schema::table('user_profiles', function (Blueprint $table): void {
                $table->timestamp('onboarding_completed_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // Compatibility migration: intentionally non-destructive.
        // Some installations may already have a subset of these columns.
    }
};
