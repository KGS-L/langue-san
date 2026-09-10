<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consent_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->longText('content');
            $table->boolean('allow_training')->default(false);
            $table->boolean('allow_audio_publication')->default(false);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consent_version_id')->constrained()->restrictOnDelete();
            $table->timestamp('accepted_at');
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'consent_version_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_consents');
        Schema::dropIfExists('consent_versions');
    }
};
