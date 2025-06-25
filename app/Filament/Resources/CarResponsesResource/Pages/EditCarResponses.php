<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use Filament\Actions;
use App\Helpers\ImageHelper;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CarResponsesResource;

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

    protected function mutateFormDataBeforeSave(array $data): array {
        $data['img_after'] = ImageHelper::convertToUrl($data['img_after_path'] ?? null);
        return $data;
    }
}
