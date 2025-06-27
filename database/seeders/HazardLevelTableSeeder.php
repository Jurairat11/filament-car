<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HazardLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hazard_levels')->insert([
            [
                'id' => '1',
                'level_name' => 'ระดับ A',
                'level_desc' => 'อาจทำให้เสียชีวิตหรือสูญเสียอวัยวะ (แก้ไขชั่วคราวทันที - แก้ไขถาวร 3 วัน)',
                'due_days' => 3
            ],
            [
                'id' => '2',
                'level_name' => 'ระดับ B',
                'level_desc' => 'อาจทำให้เกิดการบาดเจ็บรุนแรง ถึงขั้นหยุดงาน (แก้ไขชั่วคราวทันที - แก้ไขถาวร 5 วัน)',
                'due_days' => 5
            ],
            [
                'id' => '3',
                'level_name' => 'ระดับ C',
                'level_desc' => 'อาจทำให้เกิดการบาดเจ็บเล็กน้อย ไม่หยุดงาน (แก้ไขชั่วคราวทันที - แก้ไขถาวร 7 วัน)',
                'due_days' => 7
            ],
        ]);
    }
}
