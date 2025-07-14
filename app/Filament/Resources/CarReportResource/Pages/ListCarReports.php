<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CarReportResource;
use App\Models\Car_report;

class ListCarReports extends ListRecords
{
    protected static string $resource = CarReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        Actions\CreateAction::make('Create')
            ->label('Create'),
        ];
    }
    public function getTabs(): array
    {
        $year = now()->year;
        return [
            'all' => Tab::make('All')
                ->badge(Car_report::whereYear('created_at',$year)
                ->count()),
            'draft' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'draft')->count())
                ->badgeColor('gray')
                ->query(fn ($query) => $query->where('status', 'draft')),
            'reported' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'reported')->count())
                ->badgeColor('info')
                ->query(fn ($query) => $query->where('status', 'reported')),
            'on progress' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'on_process')->count())
                ->badgeColor('warning')
                ->query(fn ($query) => $query->where('status', 'on_process')),
            'pending review' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'pending_review')->count())
                ->badgeColor('success')
                ->query(fn ($query) => $query->where('status', 'pending_review')),
            'reopened' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'reopened')->count())
                ->badgeColor('warning')
                ->query(fn ($query) => $query->where('status', 'reopened')),
            'closed' => Tab::make()
                ->badge(Car_report::whereYear('created_at',$year)
                ->where('status', 'closed')->count())
                //->badge(Car_report::query()->where('status', 'closed')->count())
                ->badgeColor('gray')
                ->query(fn ($query) => $query->where('status', 'closed')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'active';
    }
}
