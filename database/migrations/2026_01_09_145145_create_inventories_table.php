<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('inventories', function (Blueprint $table) {
      $table->id(); // ✅ internal id

      // dari API
      $table->string('external_id')->nullable()->unique(); // simpan "_id"
      $table->string('external_location_id')->nullable()->index(); // simpan location_id dari Mile
      $table->string('organization_id')->nullable();

      $table->string('location_code')->nullable()->index();
      $table->string('code')->nullable()->index(); // item code
      $table->string('name')->nullable();

      $table->integer('qty')->nullable();
      $table->integer('qty_goods')->nullable();
      $table->integer('qty_available')->nullable();
      $table->integer('qty_incoming')->nullable();
      $table->integer('qty_outgoing')->nullable();

      $table->string('stock_status')->nullable();
      $table->string('created_by')->nullable();

      $table->dateTime('api_created_at')->nullable();
      $table->dateTime('api_updated_at')->nullable();
      $table->dateTime('last_calculated')->nullable();

      $table->string('rack_type')->nullable();

      // simpan object nested biar "simpan semua datanya"
      $table->json('location_payload')->nullable();
      $table->json('product_payload')->nullable();
      $table->json('custom_field')->nullable();
      $table->json('raw_payload')->nullable();

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('inventories');
  }
};
