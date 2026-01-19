<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // item identity
            $table->string('code');
            $table->string('serial_number')->nullable();

            // qty
            $table->integer('qty')->default(0);
            $table->integer('qty_process')->default(0);

            // rack & location
            $table->string('rack')->nullable();
            $table->string('rack_source')->nullable();

            $table->string('external_location_id')->nullable();
            $table->string('external_location_id_source')->nullable();

            // outbound ref
            $table->string('ref_number_outbound')->nullable();

            // status
            $table->string('status')->nullable();

            // payload
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['code', 'serial_number']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}
