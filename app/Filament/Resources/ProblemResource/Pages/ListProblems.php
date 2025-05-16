<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProblemResource;
use App\Models\Problem;

class ListProblems extends ListRecords
{
    protected static string $resource = ProblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('Create')
            ->label('Create'),
        ];
    }

}
