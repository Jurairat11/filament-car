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
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;
        $deptId = Auth::user()->dept_id;

        $delayCarIds = Car_responses::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
            ->where('status_reply', 'delay')
            ->whereHas('carReport', fn($q) => $q->where('responsible_dept_id', $deptId))
            ->pluck('car_id')
            ->unique();

        $stats = [
        'total' => Car_report::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
            ->where('responsible_dept_id', $deptId)
            ->whereNot('status','draft')
            ->count(),

        'closed' => Car_report::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
            ->where('status', 'closed')
            ->where('responsible_dept_id', $deptId)
            ->count(),

        'delay' => $delayCarIds->count(),

        'on_process' => Car_report::when($start, fn($q) => $q->whereDate('created_at', '>', $start))
            ->when($end, fn($q) => $q->whereDate('created_at', '<', $end))
            ->whereNot('status', 'closed')
            ->where('responsible_dept_id', $deptId)
            ->whereNotIn('id', $delayCarIds)
            ->count(),
        ];

        return [

        Stat::make('Total Hazard', $stats['total'])
            ->description('Total created CAR report.')
            ->descriptionIcon('heroicon-m-document-text', IconPosition::Before)
            ->chart([3, 5, 2, 4, 6, 1, 7])
            ->color('info'),

        Stat::make('Finished CAR', $stats['closed'])
            ->description('Number of Finished CAR report.')
            ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
            ->chart([3, 5, 2, 4, 6, 1, 7])
            ->color('success'),

        Stat::make('On Process CAR', $stats['on_process'])
            ->description('Number of On Process CAR report.')
            ->descriptionIcon('heroicon-m-clock', IconPosition::Before)
            ->chart([3, 5, 2, 4, 6, 1, 7])
            ->color('warning'),

        Stat::make('Delay CAR', $stats['delay'])
            ->description('Number of Delay CAR report.')
            ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
            ->chart([3, 5, 2, 4, 6, 1, 7])
            ->color('danger'),

        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('User');
    }
}



