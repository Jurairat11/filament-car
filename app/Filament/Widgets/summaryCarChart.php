<?php

// namespace App\Filament\Widgets;

// use Carbon\Carbon;
// use App\Models\Car_report;
// use Flowframe\Trend\Trend;
// use Flowframe\Trend\TrendValue;
// use Filament\Widgets\ChartWidget;
// use Filament\Widgets\Concerns\InteractsWithPageFilters;

// class summaryCarChart extends ChartWidget
// {
//     use InteractsWithPageFilters;
//     protected static ?string $heading = 'Chart';
//     protected static ?string $maxHeight = '300px';
//     protected static ?int $sort = 6;
//     protected static bool $isLazy = false;
//     protected function getData(): array
//     {
//         $start = $this->filters['startDate'];
//         $end = $this->filters['endDate'];
//         $dept = $this->filters['dept_id'];
//         $data = Trend::query(Car_report::where('status', 'closed')
//         ->where('responsible_dept_id', $dept))
//         ->between(
//         $start ? Carbon::parse($start) : now()->startOfYear(),
//             $end ? Carbon::parse($end) : now()->endOfYear(),
//         )
//         ->perMonth()
//         ->count();

//     return [
//         'datasets' => [
//             [
//                 'label' => 'Car Closed',
//                 'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
//             ],
//         ],
//         'labels' => $data->map(fn (TrendValue $value) =>
//             Carbon::parse($value->date)->format('M')),

//     ];
// }
//     protected function getOptions(): array
//     {
//         return [
//             'scales' => [
//                 'y' => [
//                     'beginAtZero' => true,
//                     'min' => 0,
//                     'max' => 20,
//                     'ticks' => [
//                         'stepSize' => 5,
//                     ],
//                 ],
//             ],
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar';
//     }

// }
