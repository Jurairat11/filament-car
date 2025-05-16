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
            'accepted' => Tab::make()
            ->badge(Car_report::query()->where('status', 'accepted')->count())
            ->badgeColor('success'),
            'reported' => Tab::make()
            ->badge(Car_report::query()->where('status', 'reported')->count())
            ->badgeColor('info'),
            'in progress' => Tab::make()
            ->badge(Car_report::query()->where('status', 'in_progress')->count())
            ->badgeColor('warning'),
            'pending review' => Tab::make()
            ->badge(Car_report::query()->where('status', 'pending_review')->count())
            ->badgeColor('success'),
            'reopened' => Tab::make()
            ->badge(Car_report::query()->where('status', 'reopened')->count())
            ->badgeColor('warning'),
            'closed' => Tab::make()
            ->badge(Car_report::query()->where('status', 'closed')->count())
            ->badgeColor('gray'),
        ];
    }
    public function getDefaultActiveTab(): string | int | null
    {
        return 'active';
    }
}
