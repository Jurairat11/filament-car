<?php

namespace App\Filament\Resources\HazardSourceResource\Pages;

use App\Filament\Resources\HazardSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardSources extends ListRecords
{
    protected static string $resource = HazardSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Create'),
        ];
    }
}
