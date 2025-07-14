<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use App\Models\Car_responses;
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

            // Stat::make('On Process CAR',
            // Car_report::when(
            //     $start,
            //         fn ($query)=> $query->whereDate('created_at', '>',$start)
            //     ->when(
            //     $end,
            //         fn($query)=> $query->whereDate('created_at','<',$end)
            // ))
            // ->whereNot('status','closed')
            // ->where('responsible_dept_id', Auth::user()->dept_id)->count())
            // ->description('Number of on process car report.')
            //     ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
            //     ->chart([7, 2, 10, 3, 15, 4, 17])
            //     ->color('warning'),

            // Step 1: get delay car IDs
            $delayCarIds = Car_responses::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
                ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
                ->where('status_reply', 'delay')
                ->whereHas('carReport', function ($q) {
                    $q->where('responsible_dept_id', Auth::user()->dept_id);
                })
                ->pluck('car_id')
                ->unique(),

            // Step 2: Count true On Process (not closed, and not delay)
            $onProcessCount = Car_report::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
                ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
                ->whereNot('status', 'closed')
                ->where('responsible_dept_id', Auth::user()->dept_id)
                ->whereNotIn('id', $delayCarIds)
                ->count(),

                // Step 3: Count Delay (already queried above)
            $delayCount = $delayCarIds->count(),

            Stat::make('On Process CAR', $onProcessCount)
                ->description('Number of on process car report.')
                ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),

            Stat::make('Delay CAR', $delayCount)
                ->description('Number of delay car report.')
                ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),

            // delay status
            // Stat::make('Delay CAR',
            // Car_responses::when(
            //     $start,
            //         fn ($query)=> $query->whereDate('created_at', '>',$start)
            //     ->when(
            //     $end,
            //     fn($query)=> $query->whereDate('created_at','<',$end)
            // ))
            // ->where('status_reply','delay')
            // ->whereHas('carReport', function ($q) {
            //     $q->where('responsible_dept_id', Auth::user()->dept_id);
            // })
            // ->count())
            // ->description('Number of delay car report.')
            //     ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
            //     ->chart([7, 2, 10, 3, 15, 4, 17])
            //     ->color('danger'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('User');
    }
}
