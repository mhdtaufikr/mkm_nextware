<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name'              => 'Administrator',
            'username'          => 'admin',
            'email'             => 'admin@ptmkm.co.id',
            'email_verified_at' => Carbon::now(),
            'password'          => Hash::make('Password.1'),
            'remember_token'    => null,

            'role'              => 'admin',
            'location'          => 'HQ',

            'last_login'        => null,
            'login_counter'     => 0,
            'is_active'         => true,

            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
    }
}
