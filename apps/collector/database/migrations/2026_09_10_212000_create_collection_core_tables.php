<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->text('french_text');
            $table->text('context')->nullable();
            $table->string('type', 30)->index();
            $table->unsignedTinyInteger('difficulty')->default(1)->index();
            $table->unsignedSmallInteger('priority')->default(10)->index();
            $table->unsignedSmallInteger('target_contributions')->default(3);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('collection_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contributor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('started')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('session_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('status', 30)->default('pending')->index();
            $table->timestamps();
            $table->unique(['collection_session_id', 'prompt_id']);
            $table->unique(['collection_session_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_prompts');
        Schema::dropIfExists('collection_sessions');
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('categories');
    }
};
