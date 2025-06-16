<?php

namespace App\Filament\Widgets;

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
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];

        return [
            Stat::make('Total Hazard',
            Car_report::when(
            $start,
            fn ($query)=> $query->whereDate('created_at', '>',$start)
            ->when(
            $end,
            fn($query)=> $query->whereDate('created_at','<',$end)
            ))
                ->where('responsible_dept_id', Auth::user()->dept_id)->count())

                ->description('Total created car report.')
                ->descriptionIcon('heroicon-m-document-text', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('Completed CAR',
            Car_report::when(
                $start,
                    fn ($query)=> $query->whereDate('created_at', '>',$start)
                ->when(
                $end,
                fn($query)=> $query->whereDate('created_at','<',$end)
            ))
            ->where('status','closed')
            ->where('responsible_dept_id', Auth::user()->dept_id)->count())
            ->description('Number of closed car report.')
                ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('In Progress CAR',
            Car_report::when(
                $start,
                    fn ($query)=> $query->whereDate('created_at', '>',$start)
                ->when(
                $end,
                    fn($query)=> $query->whereDate('created_at','<',$end)
            ))
            ->whereNot('status','closed')
            ->where('responsible_dept_id', Auth::user()->dept_id)->count())
            ->description('Number of in progress car report.')
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('User');
    }
}
