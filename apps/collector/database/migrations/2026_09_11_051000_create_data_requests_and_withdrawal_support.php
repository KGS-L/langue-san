<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->timestamp('withdrawn_at')->nullable()->index()->after('submitted_at');
        });

        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contributor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contribution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->index();
            $table->string('status', 30)->default('pending')->index();
            $table->text('details')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_requests');

        Schema::table('contributions', function (Blueprint $table) {
            $table->dropIndex(['withdrawn_at']);
            $table->dropColumn('withdrawn_at');
        });
    }
};
