<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use App\Models\Car_responses;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;
    protected function getStats(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        // $active = $this->filters['active'];
        $dept = $this->filters['dept_id'];


        return [
        Stat::make('Total Hazard',
        Car_report::when(
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
        //->where('responsible_dept_id', $dept)
        ->count()
        )
        ->description('Total created CAR report.')
        ->descriptionIcon('heroicon-m-document-text', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('info'),

        Stat::make('CAR Completed',Car_report::when(
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
        ->where('status', 'closed')
        ->count()
        )
        ->description('Number of closed CAR report.')
        ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('success'),

        Stat::make('CAR On progress',Car_report::when(
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
        ->whereNot('status', 'closed')
        // ->where('responsible_dept_id', $dept)
        ->count()
        )
        ->description('Number of on progress CAR report.')
        ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('warning'),

        Stat::make('CAR Delay', Car_responses::when(
        $start,
        fn ($query) => $query->whereDate('created_at', '>', $start)
        )
        ->when(
            $end,
            fn ($query) => $query->whereDate('created_at', '<', $end)
        )
        ->when(
            $dept,
            fn ($query) => $query->whereHas('carReport', function ($q) use ($dept) {
                $q->where('responsible_dept_id', $dept);
            })
        )
        ->where('status_reply', 'delay')
        ->count()
        )
        ->description('Number of delay CAR report.')
        ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('danger'),
        ];
    }
    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
