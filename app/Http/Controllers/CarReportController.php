<?php

namespace App\Http\Controllers;

use App\Models\Car_report;
use Illuminate\Http\Request;

class CarReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $maxRetries = 3;
        $retry = 0;

        while ($retry < $maxRetries) {
            try {
                // Generate car_no อย่างปลอดภัย
                $data['car_no'] = Car_report::generateCarNo();

                // สร้าง CarReport ด้วยข้อมูลทั้งหมด (รวม car_no)
                $carReport = Car_report::create($data);

                return response()->json($carReport, 201);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23505') { // PostgreSQL duplicate key
                    $retry++;
                    usleep(100000); // รอ 0.1 วินาทีก่อน retry
                } else {
                    throw $e;
                }
            }
        }

        return response()->json(['error' => 'ไม่สามารถสร้าง car_no ได้เนื่องจากซ้ำซ้อน'], 500);
    }
}
