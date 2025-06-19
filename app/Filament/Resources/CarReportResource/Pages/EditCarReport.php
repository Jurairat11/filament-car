<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use App\Filament\Resources\CarReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarReport extends EditRecord
{
    protected static string $resource = CarReportResource::class;
    protected static ?string $title = 'Edit CAR Report';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'CAR updated';
    }
}
