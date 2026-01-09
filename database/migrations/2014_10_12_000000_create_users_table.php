<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');                  // Full name
            $table->string('username')->unique();    // Username
            $table->string('email')->unique();       // Email
            $table->timestamp('email_verified_at')->nullable();

            // Auth
            $table->string('password');
            $table->rememberToken();

            // Role & assignment
            $table->string('role')->nullable();      // admin
            $table->string('location')->nullable();  // HQ, Plant A, Warehouse Cikarang

            // Activity
            $table->dateTime('last_login')->nullable();
            $table->unsignedInteger('login_counter')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
