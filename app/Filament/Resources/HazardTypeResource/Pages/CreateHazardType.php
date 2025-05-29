<?php

namespace App\Filament\Resources\HazardTypeResource\Pages;

use App\Filament\Resources\HazardTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardType extends CreateRecord
{
    protected static string $resource = HazardTypeResource::class;
    protected static ?string $title = 'Create Hazard Type';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Hazard type created';
    }
}
