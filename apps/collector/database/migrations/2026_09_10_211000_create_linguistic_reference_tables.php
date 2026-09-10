<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('varieties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code', 10)->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('localities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('province')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->foreignId('suggested_variety_id')->nullable()->constrained('varieties')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // A contributor profile is the collection identity. It may be anonymous
        // (user_id = null) and can later be linked to an authenticated account.
        Schema::create('contributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('public_code', 32)->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('guest_token_hash', 64)->nullable()->unique();
            $table->foreignId('locality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('locality_other')->nullable();
            $table->string('fluency_level', 30)->nullable();
            $table->boolean('can_write_san')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_profiles');
        Schema::dropIfExists('localities');
        Schema::dropIfExists('varieties');
    }
};
