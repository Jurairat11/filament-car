<?php

namespace App\Filament\Resources\HazardLevelResource\Pages;

use App\Filament\Resources\HazardLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHazardLevel extends CreateRecord
{
    protected static string $resource = HazardLevelResource::class;
    protected static ?string $title = 'Create Hazard Level';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Hazard level created';
    }
}
