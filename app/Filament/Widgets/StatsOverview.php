<?php

namespace App\Filament\Widgets;

use App\Models\Problem;
use App\Models\Car_report;
use Illuminate\Support\HtmlString;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }
    protected function getStats(): array
    {
        return [
        //Stat::make('Total CAR', $this->totalCAR = Car_report::count()),

        ];
    }
}
