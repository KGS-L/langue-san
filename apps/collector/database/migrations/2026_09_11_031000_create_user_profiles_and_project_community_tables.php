<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('age_range', 30)->nullable();
            $table->string('profession', 80)->nullable()->index();
            $table->string('profession_other', 150)->nullable();
            $table->string('organization', 180)->nullable();
            $table->string('avatar_path')->nullable();
            $table->boolean('public_profile_enabled')->default(false)->index();
            $table->string('public_display_name', 120)->nullable();
            $table->text('public_bio')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('project_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('contribution_areas');
            $table->text('experience');
            $table->text('motivation');
            $table->string('availability', 40)->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('san_connection')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('project_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('approved_application_id')->nullable()->constrained('project_applications')->nullOnDelete();
            $table->json('contribution_areas');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_memberships');
        Schema::dropIfExists('project_applications');
        Schema::dropIfExists('user_profiles');
    }
};
