<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('locality_id')->nullable()->constrained()->nullOnDelete();
            $table->mediumText('san_text')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();
            $table->index(['prompt_id', 'status']);
        });

        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('quality_status', 30)->nullable()->index();
            $table->timestamps();
        });

        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validator_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision', 30)->index();
            $table->mediumText('san_text_corrected')->nullable();
            $table->foreignId('variety_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['contribution_id', 'validator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validations');
        Schema::dropIfExists('recordings');
        Schema::dropIfExists('contributions');
    }
};
