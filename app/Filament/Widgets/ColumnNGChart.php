<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
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
    protected function getOptions(): array
    {

        $data = Car_report::selectRaw('dept_id, COUNT(*) as total')
        ->groupBy('dept_id')
        ->with('department')
        ->get();

        // เตรียม labels (ชื่อแผนก) และ values (จำนวน)
        $categories = $data->map(fn($item) => optional($item->department)->dept_name ?? 'Unknown')->toArray();
        $values = $data->map(fn($item) => $item->total)->toArray();

        return [
                'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'จำนวนปัญหา',
                    'data' => $values,
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
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
