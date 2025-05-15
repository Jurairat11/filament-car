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
            'emp_id' => 'admin01',
            'emp_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@example.com',
            'dept_id' => 1,
            'password' => Hash::make('admin1234'),
        ]);
        $user1->assignRole($adminRole);

        $user2 = User::factory()->create([
            'emp_id' => 'she12',
            'emp_name' => 'Jennie',
            'last_name' => 'Kim',
            'email' => 'jennie@example.com',
            'dept_id' => 2,
            'password' => Hash::make('she1234'),
        ]);
        $user2->assignRole($safetyRole);



    }
}
