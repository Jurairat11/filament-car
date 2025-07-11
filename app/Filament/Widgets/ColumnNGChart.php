<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use App\Models\Car_responses;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ColumnNGChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'columnNGChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'NG Patrol';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */

    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;
    protected function getOptions(): array
{
    // $departments = Department::orderBy('dept_name')
    // ->whereNot('dept_name','IT')
    // ->get();

    $generalDepartments = Department::orderBy('dept_name')
    ->where('group','general')
    ->get();

    $otherDept = Department::where('group','other')
    ->pluck('dept_id')->toArray();

    // ดึงจำนวน car report ทั้งหมด group by dept_id
    $totalCounts = Car_report::selectRaw('responsible_dept_id, COUNT(*) as total')
        ->groupBy('responsible_dept_id')
        ->pluck('total', 'responsible_dept_id');

    // ดึงจำนวน car report ที่ status = closed group by dept_id
    $closedCounts = Car_report::selectRaw('responsible_dept_id, COUNT(*) as total')
        ->where('status', 'closed')
        ->groupBy('responsible_dept_id')
        ->pluck('total', 'responsible_dept_id');

    $onProcessCounts = Car_report::selectRaw('responsible_dept_id, COUNT(*) as total')
        ->whereNot('status', 'closed')
        ->groupBy('responsible_dept_id')
        ->pluck('total', 'responsible_dept_id');

    $delayCounts = Car_responses::where('status_reply', 'delay')
        ->whereHas('carReport') // ตรวจสอบว่ามีความสัมพันธ์
        ->join('car_reports', 'car_responses.car_id', '=', 'car_reports.id')
        ->selectRaw('car_reports.responsible_dept_id, COUNT(*) as total')
        ->groupBy('car_reports.responsible_dept_id')
        ->pluck('total', 'car_reports.responsible_dept_id');

    // เตรียม labels (ชื่อแผนก) และ values (จำนวน)
    // $categories = $departments->map(fn($dept) => $dept->dept_name)->toArray();
    // $totalValues = $departments->map(fn($dept) => $totalCounts[$dept->dept_id] ?? 0)->toArray();
    // $closedValues = $departments->map(fn($dept) => $closedCounts[$dept->dept_id] ?? 0)->toArray();
    // $onProcessValues = $departments->map(fn($dept) => $onProcessCounts[$dept->dept_id] ?? 0)->toArray();
    // $delayValues = $departments->map(fn($dept) => $delayCounts[$dept->dept_id] ?? 0)->toArray();

    // เตรียม labels และ values สำหรับแผนกในกลุ่ม general
    $categories = $generalDepartments->map(fn($dept) => $dept->dept_name)->toArray();
    $totalValues = $generalDepartments->map(fn($dept) => $totalCounts[$dept->dept_id] ?? 0)->toArray();
    $closedValues = $generalDepartments->map(fn($dept) => $closedCounts[$dept->dept_id] ?? 0)->toArray();
    $onProcessValues = $generalDepartments->map(fn($dept) => $onProcessCounts[$dept->dept_id] ?? 0)->toArray();
    $delayValues = $generalDepartments->map(fn($dept) => $delayCounts[$dept->dept_id] ?? 0)->toArray();

    // 👇 เพิ่มข้อมูลของกลุ่ม 'Other' เป็นแท่งเดียว
    $otherLabel = 'Other';

    $totalValues[] = collect($otherDept)->sum(fn($id) => $totalCounts[$id] ?? 0);
    $closedValues[] = collect($otherDept)->sum(fn($id) => $closedCounts[$id] ?? 0);
    $onProcessValues[] = collect($otherDept)->sum(fn($id) => $onProcessCounts[$id] ?? 0);
    $delayValues[] = collect($otherDept)->sum(fn($id) => $delayCounts[$id] ?? 0);
    $categories[] = $otherLabel;

    return [
        'chart' => [
            'type' => 'bar',
            'height' => 300,
        ],
        'series' => [
            [
                'name' => 'Total',
                'data' => $totalValues,
            ],
            [
                'name' => 'Completed',
                'data' => $closedValues,
            ],
            [
                'name' => 'On Process',
                'data' => $onProcessValues,
            ],
            [
                'name' => 'Delay',
                'data' => $delayValues,
            ],
        ],
        'xaxis' => [
            'categories' => $categories,
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ],
        'yaxis' => [
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'beginAtZero' => true,
                        'min' => 0,
                        'max' => 30,
                        'ticks' => [
                            'stepSize' => 2,
                        ],
        ],
        'colors' => ['#3b82f6', '#10b981', '#f59e0b','#ef4444'], // สามารถเปลี่ยนสีได้ตามต้องการ
    ];
}

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
