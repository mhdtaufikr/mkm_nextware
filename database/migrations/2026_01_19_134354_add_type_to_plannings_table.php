<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('plannings', function (Blueprint $table) {

            // type: inbound | outbound
            if (!Schema::hasColumn('plannings', 'type')) {
                $table->string('type', 20)
                      ->after('code')
                      ->comment('inbound | outbound');
            }

            /**
             * Index khusus untuk kebutuhan OTDP
             * DIBUAT RINGKAS supaya tidak kena 1071
             */
            $table->index(
                ['location_code', 'type', 'plan_date'],
                'idx_plannings_location_type_date'
            );
        });
    }

    public function down(): void
    {
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropIndex('idx_plannings_location_type_date');
            $table->dropColumn('type');
        });
    }
};
