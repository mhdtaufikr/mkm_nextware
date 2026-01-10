<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('status');
        });

        // Optional: backfill dari status (kalau status=1 => active=1, selain itu 0)
        DB::table('locations')
            ->whereNotNull('status')
            ->update(['active' => DB::raw('IF(status = 1, 1, 0)')]);

        // Kalau ada status null dan mau default false, uncomment:
        // DB::table('locations')->whereNull('status')->update(['active' => 0]);
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
