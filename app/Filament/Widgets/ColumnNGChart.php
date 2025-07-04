<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
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
    $departments = Department::orderBy('dept_name')->get();

    // ดึงจำนวน car report ทั้งหมด group by dept_id
    $totalCounts = Car_report::selectRaw('dept_id, COUNT(*) as total')
        ->groupBy('dept_id')
        ->pluck('total', 'dept_id');

    // ดึงจำนวน car report ที่ status = closed group by dept_id
    $closedCounts = Car_report::selectRaw('dept_id, COUNT(*) as total')
        ->where('status', 'closed')
        ->groupBy('dept_id')
        ->pluck('total', 'dept_id');

    // เตรียม labels (ชื่อแผนก) และ values (จำนวน)
    $categories = $departments->map(fn($dept) => $dept->dept_name)->toArray();
    $totalValues = $departments->map(fn($dept) => $totalCounts[$dept->dept_id] ?? 0)->toArray();
    $closedValues = $departments->map(fn($dept) => $closedCounts[$dept->dept_id] ?? 0)->toArray();

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
        ],
        'xaxis' => [
            'categories' => $categories,
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'beginAtZero' => true,
                        'min' => 0,
                        'max' => 10,
                        'ticks' => [
                            'stepSize' => 2,
                        ],
        ],
        'yaxis' => [
            'labels' => [
                'style' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ],
        'colors' => ['#3b82f6', '#10b981'], // สามารถเปลี่ยนสีได้ตามต้องการ
    ];
}

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
