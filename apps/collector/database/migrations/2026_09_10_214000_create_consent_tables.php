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

        Schema::create('contributor_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contributor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consent_version_id')->constrained()->restrictOnDelete();
            $table->timestamp('accepted_at');
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->unique(['contributor_profile_id', 'consent_version_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_consents');
        Schema::dropIfExists('consent_versions');
    }
};
