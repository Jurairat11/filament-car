<?php

namespace App\Filament\Resources\HazardLevelResource\Pages;

use App\Filament\Resources\HazardLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHazardLevels extends ListRecords
{
    protected static string $resource = HazardLevelResource::class;
    protected static ?string $title = 'Hazard Level';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
