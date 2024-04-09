<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('roles')->insert([[
            'name'=>'admin',
        ],[
            'name'=>'user',
        ]]);

        DB::table('users')->insert([
            'name' => 'marriator',
            'email' => 'ilyaDevmarriator@gmail.com',
            'password' => Hash::make('123456'),
        ]);

        DB::table('user_roles')->insert([
            'user_id' => 1,
            'role_id'=> 1
        ]);
    }
}
