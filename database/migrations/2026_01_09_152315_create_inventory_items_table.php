<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            // external identifiers
            $table->string('external_id')->nullable()->index();           // _id dari API inventory-item
            $table->string('external_inventory_id')->nullable()->index(); // inventory_id dari API (ref ke summary inventory)
            $table->string('external_location_id')->nullable()->index();  // location_id dari API

            // business keys (buat join ke summary inventory)
            $table->string('code')->nullable()->index();
            $table->string('serial_number')->nullable()->index();

            // attributes
            $table->string('rack')->nullable();
            $table->string('rack_type')->nullable();
            $table->string('status')->nullable(); // GOOD, etc
            $table->integer('qty')->nullable();

            $table->dateTime('receive_date')->nullable();

            $table->string('location_code')->nullable();
            $table->string('product_name')->nullable();

            // payloads
            $table->json('product_payload')->nullable();
            $table->json('location_payload')->nullable();
            $table->json('custom_field')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            // optional: bantu anti double (tanpa ubah logika existing)
            // kalau serial_number selalu unik per location + code, ini bagus:
            // $table->unique(['external_location_id','code','serial_number'], 'uniq_loc_code_sn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
