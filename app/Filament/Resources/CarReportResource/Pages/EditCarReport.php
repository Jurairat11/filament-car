<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use Filament\Actions;
use App\Helpers\ImageHelper;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CarReportResource;

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

    protected function mutateFormDataBeforeSave(array $data): array {
        $data['img_before'] = ImageHelper::convertToUrl($data['img_before_path'] ?? null);
        return $data;
    }
}
