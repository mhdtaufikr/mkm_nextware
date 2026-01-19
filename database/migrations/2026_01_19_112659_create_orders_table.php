<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // external
            $table->string('external_id')->unique(); // _id dari API
            $table->string('ref_number')->nullable();

            // meta
            $table->string('type'); // inbound | outbound
            $table->string('status')->nullable();

            $table->string('customer_name')->nullable();

            // location & org
            $table->string('external_location_id')->index();
            $table->string('organization_id')->nullable();

            // summary
            $table->integer('total_item')->default(0);
            $table->decimal('total', 18, 2)->default(0);

            // dates
            $table->dateTime('external_created_at')->nullable();
            $table->dateTime('external_updated_at')->nullable();

            // payloads
            $table->json('raw_item')->nullable();
            $table->json('custom_field')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index(['type', 'external_created_at']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
