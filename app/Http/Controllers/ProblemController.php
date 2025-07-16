<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $maxRetries = 3;
        $retry = 0;

        while ($retry < $maxRetries) {
            try {
                // Generate prob_id อย่างปลอดภัย
                $data['prob_id'] = Problem::generateProbId();

                // สร้าง Problem ด้วยข้อมูลทั้งหมด (รวม prob_id)
                $problem = Problem::create($data);

                return response()->json($problem, 201);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23505') { // PostgreSQL duplicate key
                    $retry++;
                    usleep(100000); // รอ 0.1 วินาทีก่อน retry
                } else {
                    throw $e;
                }
            }
        }

        return response()->json(['error' => 'ไม่สามารถสร้าง prob_id ได้เนื่องจากซ้ำซ้อน'], 500);
    }
}
