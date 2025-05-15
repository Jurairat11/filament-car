<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use App\Filament\Resources\CarResponsesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCarResponses extends CreateRecord
{
    protected static string $resource = CarResponsesResource::class;
    protected static ?string $title = 'Create CAR Responses';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'CAR response created';
    }

}
