<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Car_report;

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
    protected static ?string $heading = 'Summary of Hazard Type';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected static ?int $sort = 6;
    protected static ?string $maxHeight = '200px';
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;

    protected function getOptions(): array
    {

        // Query counts grouped by hazard_type_id
        $data = Car_report::query()
            ->selectRaw('hazard_type_id, COUNT(*) as total')
            ->groupBy('hazard_type_id')
            ->with('hazardType') // Make sure you have this relationship
            ->get();

        // Prepare series and labels
        $series = $data->pluck('total')->toArray();
        $labels = $data->map(fn($item) => $item->hazardType->type_name ?? 'Unknown')->toArray();

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
        ];
    }
}

