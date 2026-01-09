<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_endpoints', function (Blueprint $table) {
            $table->id();

            // Master identification
            $table->string('name');                           // e.g. "SAP - Get Material"
            $table->string('code')->unique();                 // e.g. "SAP_GET_MATERIAL"
            $table->text('description')->nullable();

            // Endpoint details
            $table->string('base_url')->nullable();           // e.g. https://api.company.com
            $table->string('path')->nullable();               // e.g. /v1/materials
            $table->string('method', 10)->default('GET');     // GET/POST/PUT/DELETE
            $table->json('headers')->nullable();              // {"Authorization":"Bearer ..."}
            $table->json('params')->nullable();               // {"plant":"HQ"}
            $table->json('body_template')->nullable();        // optional request body template

            // Auth / security (simple for master)
            $table->string('auth_type')->default('none');     // none|basic|bearer|api_key
            $table->string('auth_key')->nullable();           // header key or param key
            $table->text('auth_value')->nullable();           // token/key (consider encrypt later)

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_endpoints');
    }
};
