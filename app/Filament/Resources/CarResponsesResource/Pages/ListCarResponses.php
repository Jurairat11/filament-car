<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CarResponsesResource;
use App\Models\Car_responses;

class ListCarResponses extends ListRecords
{
    protected static string $resource = CarResponsesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All')
    //         ->badge(Car_responses::count()),
    //         'reported' => Tab::make()
    //         ->badge(Car_responses::query()->where('status', 'reported')->count())
    //         ->badgeColor('info'),
    //         'in progress' => Tab::make()
    //         ->badge(Car_responses::query()->where('status', 'in_progress')->count())
    //         ->badgeColor('warning'),
    //         'pending review' => Tab::make()
    //         ->badge(Car_responses::query()->where('status', 'pending_review')->count())
    //         ->badgeColor('success'),
    //         'reopened' => Tab::make()
    //         ->badge(Car_responses::query()->where('status', 'reopened')->count())
    //         ->badgeColor('warning'),
    //         'closed' => Tab::make()
    //         ->badge(Car_responses::query()->where('status', 'closed')->count())
    //         ->badgeColor('gray'),
    //     ];
    // }
    // public function getDefaultActiveTab(): string | int | null
    // {
    //     return 'active';
    // }
}
