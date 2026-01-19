<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReorderLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_reorder_levels', function (Blueprint $table) {
            $table->id();

            // FK utama
            $table->foreignId('inventory_id')
                ->constrained('inventories')
                ->cascadeOnDelete();

            // snapshot reference (optional but useful)
            $table->string('code')->index();
            $table->string('cutting_center')->nullable()->index();

            // reorder logic
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_qty')->default(0);

            // value
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('reorder_value', 18, 2)->default(0);

            // status & audit
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_calculated_at')->nullable();

            $table->timestamps();

            // anti duplikat (1 inventory = 1 rule aktif)
            $table->unique(['inventory_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_reorder_levels');
    }
}
