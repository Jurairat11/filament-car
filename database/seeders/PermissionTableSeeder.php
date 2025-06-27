<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            //Department
            [
            'name' => 'Create Department',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Department',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Department',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Department',
            'guard_name' => 'web'
            ],
            //Section
            [
            'name' => 'Create Section',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Section',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Section',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Section',
            'guard_name' => 'web'
            ],
            //Car_report
            [
            'name' => 'Create Car Report',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Car Report',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Car Report',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Car Report',
            'guard_name' => 'web'
            ],
            //Car_responses
            [
            'name' => 'Create Car Responses',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Car Responses',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Car Responses',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Car Responses',
            'guard_name' => 'web'
            ],
            //Problem
            [
            'name' => 'Create Problem',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Problem',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Problem',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Problem',
            'guard_name' => 'web'
            ],
            //Hazard_level
            [
            'name' => 'Create Hazard Level',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Hazard Level',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Hazard Level',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Hazard Level',
            'guard_name' => 'web'
            ],
            //Hazard_type
            [
            'name' => 'Create Hazard Type',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Hazard Type',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Hazard Type',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Hazard Type',
            'guard_name' => 'web'
            ],
            //Hazard_source
            [
            'name' => 'Create Hazard Source',
            'guard_name' => 'web'

            ],
            [
            'name' => 'Update Hazard Source',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Hazard Source',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Hazard Source',
            'guard_name' => 'web'
            ],
            //Place
            [
            'name' => 'Create Place',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Update Place',
            'guard_name' => 'web'
            ],
            [
            'name' => 'Delete Place',
            'guard_name' => 'web'
            ],
            [
            'name' => 'View Place',
            'guard_name' => 'web'
            ],
        ]);
    }
}
