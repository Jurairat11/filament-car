<?php

namespace App\Livewire;

use App\Models\Car_report;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UserStatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;
    use InteractsWithPageFilters;
    protected function getStats(): array
    {

        return [
            Stat::make('Total Hazard', Car_report::where('responsible_dept_id', Auth::user()->dept_id)->count())
                ->description('Total created car report.')
                ->descriptionIcon('heroicon-m-document-text', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('Completed CAR', Car_report::where('status','closed')
            ->where('responsible_dept_id', Auth::user()->dept_id)->count())
            ->description('Number of closed car report.')
                ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('In Progress CAR', Car_report::whereNot('status','closed')
            ->where('responsible_dept_id', Auth::user()->dept_id)->count())
            ->description('Number of in progress car report.')
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
        ];
    }
}
// ->where('responsible_dept_id', $dept)
//         ->count()
