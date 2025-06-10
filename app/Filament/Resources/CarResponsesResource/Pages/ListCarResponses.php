<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use Filament\Actions;
use App\Models\Car_responses;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CarResponsesResource;

class ListCarResponses extends ListRecords
{
    protected static string $resource = CarResponsesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('Create')
            ->label('Create'),
        ];
    }

}
