<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use App\Filament\Resources\CarResponsesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarResponses extends EditRecord
{
    protected static string $resource = CarResponsesResource::class;
    protected static ?string $title = 'Edit CAR Response';

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
        return 'CAR response updated';
    }
}
