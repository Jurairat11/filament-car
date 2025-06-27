<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([

        // $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // $user1 = User::factory()->create([
        //     'emp_id' => '3052',
        //     'emp_name' => 'Jurairat',
        //     'last_name' => 'Phoempanyasap',
        //     'email' => 'jurai11j0@gmail.com',
        //     'dept_id' => 1,
        //     'password' => Hash::make('admin@vcs'),
        // ]);
        // $user1->assignRole($adminRole);

        ]);
    }
}
