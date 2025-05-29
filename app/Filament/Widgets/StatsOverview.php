<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;
    protected function getStats(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        $active = $this->filters['active'];
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
        ->description('Total created car report.')
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
        ->extraAttributes([
                'class' => 'cursor-pointer',
                'wire:click' => "\$dispatch('setStatusFilter', { filter: 'closed' })",
        ])
        ->description('Number of closed car report.')
        ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('success'),

        Stat::make('CAR In progress',Car_report::when(
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
        ->description('Number of in progress car report.')
        ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('warning'),
        ];
    }
}
