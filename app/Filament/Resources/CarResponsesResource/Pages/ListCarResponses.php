<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use App\Filament\Resources\CarResponsesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarResponses extends ListRecords
{
    protected static string $resource = CarResponsesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
