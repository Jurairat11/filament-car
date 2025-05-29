<?php

namespace App\Filament\Resources\HazardTypeResource\Pages;

use App\Filament\Resources\HazardTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHazardType extends EditRecord
{
    protected static string $resource = HazardTypeResource::class;

    protected static ?string $title = 'Edit Hazard Type';

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

}
