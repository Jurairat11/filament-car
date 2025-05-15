<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use App\Filament\Resources\CarReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarReports extends ListRecords
{
    protected static string $resource = CarReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
