<?php

namespace App\Filament\Resources\HazardSourceResource\Pages;

use App\Filament\Resources\HazardSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardSource extends EditRecord
{
    protected static string $resource = HazardSourceResource::class;
    protected static ?string $title = 'Edit Hazard Source';

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
        return 'Hazard source updated';
    }
}
