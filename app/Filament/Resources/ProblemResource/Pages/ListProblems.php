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

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All')
    //         ->badge(Problem::count()),
    //         'new' => Tab::make()
    //         ->badge(Problem::query()->where('status', 'new')->count())
    //         ->badgeColor('info'),
    //         'accepted' => Tab::make()
    //         ->badge(Problem::query()->where('status', 'accepted')->count())
    //         ->badgeColor('success'),
    //         'dismissed' => Tab::make()
    //         ->badge(Problem::query()->where('status', 'dismissed')->count())
    //         ->badgeColor('danger'),
    //         'reported' => Tab::make()
    //         ->badge(Problem::query()->where('status', 'reported')->count())
    //         ->badgeColor('warning')
    //     ];
    // }
    // public function getDefaultActiveTab(): string | int | null
    // {
    //     return 'active';
    // }
}
