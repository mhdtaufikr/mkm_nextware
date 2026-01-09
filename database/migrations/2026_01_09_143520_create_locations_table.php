<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id(); // ✅ ID internal kita

            // ID dari Mile (boleh disimpan untuk mapping)
            $table->string('external_id')->nullable()->unique(); // simpan "_id"

            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();

            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();

            $table->text('address')->nullable();
            $table->string('phone')->nullable();

            $table->string('location_type')->nullable();
            $table->string('location_code')->nullable();

            $table->boolean('is_default')->default(false);
            $table->integer('zip_code')->nullable();
            $table->string('timezone')->nullable();

            $table->string('organization_id')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->bigInteger('amount_balance')->nullable();
            $table->integer('total_user')->nullable();
            $table->boolean('is_enable_wallet')->default(false);

            // simpan raw json kalau mau (custom_field/attributes dll)
            $table->json('raw_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
