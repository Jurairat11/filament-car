<?php

namespace App\Http\Controllers;

use App\Models\Car_report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarReportController extends Controller
{
    // public function CreateCarReport(Request $request)
    // {
    //     try {
    //         $report = DB::transaction(function () use ($request) {
    //             // นับแบบ lock เพื่อไม่ให้ซ้ำ
    //             $count = Car_report::whereYear('created_at', now()->year)->lockForUpdate()->count() + 1;
    //             $carNo = 'C-' . now()->format('y') . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);

    //             $data = $request->all();
    //             $data['car_no'] = $carNo;

    //             return Car_report::create($data);
    //         });

    //         return response()->json($report, 201);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'สร้างไม่สำเร็จ: ' . $e->getMessage()], 500);
    //     }
    // }
}
