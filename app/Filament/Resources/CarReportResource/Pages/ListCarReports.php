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
        return [
            'all' => Tab::make('All')
            ->badge(Car_report::count()),
            'draft' => Tab::make()
            ->badge(Car_report::query()->where('status', 'draft')->count())
            ->badgeColor('gray')
            ->query(fn ($query) => $query->where('status', 'draft')),
            'reported' => Tab::make()
            ->badge(Car_report::query()->where('status', 'reported')->count())
            ->badgeColor('info')
            ->query(fn ($query) => $query->where('status', 'reported')),
            'in progress' => Tab::make()
            ->badge(Car_report::query()->where('status', 'in_progress')->count())
            ->badgeColor('warning')
            ->query(fn ($query) => $query->where('status', 'in_progress')),
            'pending review' => Tab::make()
            ->badge(Car_report::query()->where('status', 'pending_review')->count())
            ->badgeColor('success')
            ->query(fn ($query) => $query->where('status', 'pending_review')),
            'reopened' => Tab::make()
            ->badge(Car_report::query()->where('status', 'reopened')->count())
            ->badgeColor('warning')
            ->query(fn ($query) => $query->where('status', 'reopened')),
            'closed' => Tab::make()
            ->badge(Car_report::query()->where('status', 'closed')->count())
            ->badgeColor('gray')
            ->query(fn ($query) => $query->where('status', 'closed')),
        ];
    }
    public function getDefaultActiveTab(): string | int | null
    {
        return 'active';
    }
}
