<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //User::factory(10)->create();

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $safetyRole = Role::firstOrCreate(['name' => 'Safety']);

        $user1 = User::factory()->create([
            'emp_id' => '3052',
            'emp_name' => 'Jurairat',
            'last_name' => 'Phoempanyasap',
            'email' => 'jurai11j0@gmail.com',
            'dept_id' => 1,
            'password' => Hash::make('admin@vcs'),
        ]);
        $user1->assignRole($adminRole);

        $user2 = User::factory()->create([
            'emp_id' => '2412',
            'emp_name' => 'Jennie',
            'last_name' => 'Kim',
            'email' => 'jennie01@example.com',
            'dept_id' => 2,
            'password' => Hash::make('she1234'),
        ]);
        $user2->assignRole($safetyRole);

    }
}
