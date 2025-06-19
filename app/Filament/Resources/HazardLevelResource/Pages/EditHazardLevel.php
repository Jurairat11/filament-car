<?php

namespace App\Filament\Resources\HazardLevelResource\Pages;

use App\Filament\Resources\HazardLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardLevel extends EditRecord
{
    protected static string $resource = HazardLevelResource::class;
    protected static ?string $title = 'Edit Hazard Level';

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
        return 'Hazard level updated';
    }

}
