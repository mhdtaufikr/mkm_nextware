<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();

            // master dari view
            $table->string('location_code', 255);
            $table->text('cutting_center');

            // tipe data: planning / receiving / delivery
            $table->string('type', 50)->default('planning');

            // tanggal per hari
            $table->date('plan_date');

            // qty input user
            $table->integer('qty')->default(0);

            $table->timestamps();

            // prevent duplicate row untuk kombinasi yang sama
            $table->unique(['location_code', 'type', 'plan_date', 'cutting_center'], 'uq_planning_key');

            $table->index(['location_code', 'type', 'plan_date'], 'idx_planning_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
