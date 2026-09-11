<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('localities', function (Blueprint $table) {
            $table->string('suggested_variety_status', 30)->nullable()->after('suggested_variety_id');
            $table->text('suggested_variety_source')->nullable()->after('suggested_variety_status');
        });
    }

    public function down(): void
    {
        Schema::table('localities', function (Blueprint $table) {
            $table->dropColumn(['suggested_variety_status', 'suggested_variety_source']);
        });
    }
};
