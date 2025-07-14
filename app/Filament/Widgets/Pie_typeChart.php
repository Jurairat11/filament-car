<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use Illuminate\Support\Js;

use Illuminate\Support\Facades\Auth;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class Pie_typeChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'pieTypeChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Summary Hazard Classified by Type (Case)';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '500px';
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 3;

    use InteractsWithPageFilters;

    protected function getOptions(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        $dept = $this->filters['dept_id'];

        // Query counts grouped by hazard_type_id
        $data = Car_report::when(
            $start,
            fn ($query)=> $query->whereDate('created_at', '>',$start)
        )
        ->when(
            $end,
            fn($query)=> $query->whereDate('created_at','<',$end)
        )
        ->when(
            $dept,
            fn($query)=> $query->where('responsible_dept_id',$dept)

        )
        ->selectRaw('hazard_type_id, COUNT(*) as total')
        ->groupBy('hazard_type_id')
        ->with('hazardType') // Make sure you have this relationship
        ->get();

        // Prepare series and labels
        $series = $data->pluck('total')->toArray();
        $labels = $data->map(fn($item) => $item->hazardType->type_name ?? 'Unknown')->toArray();

        foreach ($series as $i => $count){
            $Count = $count;
        }

        if (empty($series)) {
            return [
                'chart' => [
                    'type' => 'pie',
                    'height' => 300,
                ],
                'series' => [1],
                'labels' => ['No Data'],
                'colors' => ['#f0f0f0'],
                'legend' => [
                    'labels' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ];
        }
        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'colors' => [
                '#FF4560',
                '#008FFB',
                '#00E396',
                '#775DD0',
                '#FEB019',
                '#FF4560',
                '#008FFB',
                '#00E396',
            ],
            // 'dataLabels' => [
            //     'enabled' => true,
            //     'formatter' => Js::raw('function (val ,opts) {
            //         const count = opts.w.config.series[opts.seriesIndex];
            //         return val.toFixed(1) + "%, " + count;
            //     }'),
            // ]
            'dataLabels' => [
                'enabled' => true,
                'formatter' => $Count
            ]

        ];
    }
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);

    }
}


