<?php

namespace App\Filament\Resources\HazardTypeResource\Pages;

use App\Filament\Resources\HazardTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardTypes extends ListRecords
{
    protected static string $resource = HazardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('Create')
            ->label('Create'),
        ];
    }
}
