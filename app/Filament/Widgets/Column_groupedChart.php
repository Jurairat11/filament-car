<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
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
    protected static ?string $heading = 'Summary monthly of CAR reported';

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
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];

        $seriesData = array_fill_keys($categories, 0);
        $closedData = array_fill_keys($categories, 0);
        $inprogressData = array_fill_keys($categories, 0);

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
        ->selectRaw("strftime('%m', created_at) as month, COUNT(strftime('%m', created_at)) as total")
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // $data = Car_report::query()
        //     ->selectRaw("strftime('%m', created_at) as month, COUNT(strftime('%m', created_at)) as total")
        //     ->groupBy('month')
        //     ->orderBy('month')
        //     ->get();

        // Prepare series data
        // $seriesData = [];
        // foreach ($data as $item) {
        //     array_push($seriesData, $item->total);
        //

        foreach ($data as $item) {
            $seriesData[$item->month] = $item->total;
        }

        //dd($seriesData); //05

        // return [
        //     'categories' => $categories,
        //     'seriesData' => array_values($seriesData), // Convert associative array to indexed array
        // ];

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
        ->selectRaw("strftime('%m', created_at) as month, COUNT(*) as total")
        ->where('status', 'closed')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        foreach ($data as $item) {
            $closedData[$item->month] = $item->total;
        }

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
        ->selectRaw("strftime('%m', created_at) as month, COUNT(*) as total")
        ->whereNot('status', 'closed')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        foreach ($data as $item) {
            $inprogressData[$item->month] = $item->total;
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
                    'name' => 'Completed',
                    'data' => array_values($closedData),
                ],
                [
                    'name' => 'In Progress',
                    'data' => array_values($inprogressData),
                ]
            ],
            'xaxis' => [
                'categories' => array_map(function ($month) use ($monthName) {
                    return $monthName[$month];
                }, $categories),
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
                        'max' => 50,
                        'ticks' => [
                            'stepSize' => 2,
                        ],
            ],
            'colors' => [ '#3b82f6','#10b981','#f59e0b']

        ];
    }
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
