<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use Filament\Actions;
use App\Helpers\ImageHelper;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProblemResource;

class EditProblem extends EditRecord
{
    protected static string $resource = ProblemResource::class;

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
        return 'Problem updated';
    }

    protected function mutateFormDataBeforeSave(array $data): array {
            unset($data['prob_img']);
        return $data;
    }

}
