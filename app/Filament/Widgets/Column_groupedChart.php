<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use App\Models\Car_responses;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class Column_groupedChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'columnGroupedChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Summary Hazard Classified by Permanent Countermeasure Status (Case)';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 3;

    use InteractsWithPageFilters;

    protected function getOptions(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        $dept = $this->filters['dept_id'];

        $categories = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        $monthName = [
            '01' => 'Jan.',
            '02' => 'Feb.',
            '03' => 'Mar.',
            '04' => 'Apr.',
            '05' => 'May.',
            '06' => 'Jun.',
            '07' => 'Jul.',
            '08' => 'Aug.',
            '09' => 'Sep.',
            '10' => 'Oct.',
            '11' => 'Nov.',
            '12' => 'Dec.'
        ];

        $seriesData = array_fill_keys($categories, 0);
        $closedData = array_fill_keys($categories, 0);
        $inprogressData = array_fill_keys($categories, 0);
        $delayData = array_fill_keys($categories, 0);

        // Query counts grouped by month
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
        ->selectRaw("to_char(created_at,'MM') as month, COUNT(to_char(created_at,'MM')) as total")
        ->groupBy('month')
        ->orderBy('month')
        ->get();


        foreach ($data as $item) {
            $seriesData[$item->month] = $item->total;
        }

        // Closed Status
        $data = Car_report::when(
            $start,
            fn($query)=> $query->whereDate('created_at', '>',$start)
        )
        ->when(
            $end,
            fn($query)=> $query->whereDate('created_at','<',$end)
        )
        ->when(
            $dept,
            fn($query)=> $query->where('responsible_dept_id',$dept)
        )
        ->selectRaw("to_char(created_at,'MM') as month, COUNT(*) as total")
        ->where('status', 'closed')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        foreach ($data as $item) {
            $closedData[$item->month] = $item->total;
        }

        $delayCarIds = Car_responses::where('status_reply', 'delay')->pluck('car_id')->unique();

        // query for in progress status
        $data = Car_report::when(
            $start,
            fn ($query) => $query->whereDate('created_at', '>',$start)
        )
        ->when(
            $end,
            fn($query)=>$query->whereDate('created_at','<',$end)
        )
        ->when(
            $dept,
            fn($query)=> $query->where('responsible_dept_id',$dept)
        )
        ->selectRaw("to_char(created_at,'MM') as month, COUNT(*) as total")
        ->whereNot('status', 'closed')
        ->whereNotIn('id', $delayCarIds)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        foreach ($data as $item) {
            $inprogressData[$item->month] = $item->total;
        }

        // status_reply delay
        $data = Car_responses::when(
            $start,
            fn($query)=> $query->whereDate('created_at', '>',$start)
        )
        ->when(
            $end,
            fn($query)=> $query->whereDate('created_at','<',$end)
        )
        ->when(
            $dept,
            fn ($query) => $query->whereHas('carReport', function ($q) use ($dept) {
                $q->where('responsible_dept_id', $dept);
            })
        )
        // ->selectRaw("to_char(created_at,'MM') as month, COUNT(*) as total")
        ->selectRaw("to_char(created_at,'MM') as month, COUNT(DISTINCT car_id) as total")
        ->where('status_reply', 'delay')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        foreach ($data as $item) {
            $delayData[$item->month] = $item->total;
        }


        // Return the chart options
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total',
                    'data' => array_values($seriesData),
                ],
                [
                    'name' => 'Finished',
                    'data' => array_values($closedData),
                ],
                [
                    'name' => 'On Process',
                    'data' => array_values($inprogressData),
                ],
                [
                    'name' => 'Delay',
                    'data' => array_values($delayData),
                ],
            ],
            'xaxis' => [
                'categories' => array_map(function ($month) use ($monthName) {
                    return $monthName[$month];
                }, $categories),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'colors' => '#898989'
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
                        'max' => 50,
                        'ticks' => [
                            'stepSize' => 2,
                        ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'colors' => ['#808080']
                ]
            ],
            'colors' => [ '#3b82f6','#10b981','#f59e0b','#ef4444']

        ];
    }
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}



