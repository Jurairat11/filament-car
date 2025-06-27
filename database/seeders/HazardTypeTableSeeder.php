<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HazardTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hazard_types')->insert([
            [
                'id' => '1',
                'type_name' => 'STOP 1: อันตรายจากเครื่องจักรฯ'
            ],
            [
                'id' => '2',
                'type_name' => 'STOP 2: อันตรายวัตถุหนักตกทับ'
            ],
            [
                'id' => '3',
                'type_name' => 'STOP 3: อันตรายจากยานพาหนะ'
            ],
            [
                'id' => '4',
                'type_name' => 'STOP 4: อันตรายตกจากที่สูง'
            ],
            [
                'id' => '5',
                'type_name' => 'STOP 5: อันตรายจากไฟฟ้า'
            ],
            [
                'id' => '6',
                'type_name' => 'STOP 6: อันตรายอื่นๆ'
            ],


        ]);
    }
}
