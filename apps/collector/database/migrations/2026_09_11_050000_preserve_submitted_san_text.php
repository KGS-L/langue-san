<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->mediumText('submitted_san_text')->nullable()->after('san_text');
        });

        DB::table('contributions')
            ->whereNotNull('san_text')
            ->update(['submitted_san_text' => DB::raw('san_text')]);
    }

    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropColumn('submitted_san_text');
        });
    }
};
