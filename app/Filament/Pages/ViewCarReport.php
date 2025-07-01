<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Problem;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewCarReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.view-car-report';

    public Problem $problem;

    public static function infolist(Infolist $infolist)
    {
        // Define your infolist fields here
        return $infolist
        ->schema([
            Section::make('Problem Information')
                ->visible(fn($record) => $record->problem !== null && Auth::user()?->hasAnyRole(['Admin', 'Safety']))
                ->schema([
                    Placeholder::make('prob_id')
                        ->label('Problem ID')
                        ->content(fn ($record) => $record->problem?->prob_id),

                    Placeholder::make('title')
                        ->label('Title')
                        ->content(fn ($record) => $record->problem?->title),

                    Placeholder::make('place')
                        ->label('Place')
                        ->content(fn ($record) => $record->problem?->place),

                    Placeholder::make('user_id')
                        ->label('Reporter')
                        ->content(fn ($record) => optional($record->problem?->user)->FullName),

                    Placeholder::make('dept_id')
                        ->label('Department')
                        ->content(fn ($record) => optional($record->problem?->department)->dept_name),

                    Placeholder::make('prob_desc')
                        ->label('Problem Description')
                        ->content(fn ($record) => $record->problem?->prob_desc)
                        ->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(5),
        ]);
}


}
