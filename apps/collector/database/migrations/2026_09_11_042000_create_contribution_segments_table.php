<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contribution_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('start_ms')->nullable();
            $table->unsignedInteger('end_ms')->nullable();
            $table->mediumText('san_text');
            $table->mediumText('french_translation');
            $table->foreignId('variety_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['contribution_id', 'position']);
            $table->index(['contribution_id', 'start_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_segments');
    }
};
