<?php

namespace App\Filament\Pages;

use App\Models\Problem;
use Filament\Pages\Page;
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

    public static function infolist(Infolist $infolist, Problem $problem)
    {
        // Define your infolist fields here
        return $infolist
        ->record($problem)
        ->schema([
            Section::make('Problem Information')
                ->schema([
                    // Placeholder::make('prob_id')
                    //     ->label('Problem ID')
                    //     ->content(fn ($record) => $record?->prob_id),

                    // Placeholder::make('title')
                    //     ->label('Title')
                    //     ->content(fn ($record) => $record?->title),

                    // Placeholder::make('place')
                    //     ->label('Place')
                    //     ->content(fn ($record) => $record?->place),

                    // Placeholder::make('user_id')
                    //     ->label('Reporter')
                    //     ->content(fn ($record) => optional($record?->user)->FullName),

                    // Placeholder::make('dept_id')
                    //     ->label('Department')
                    //     ->content(fn ($record) => optional($record?->department)->dept_name),

                    // Placeholder::make('prob_desc')
                    //     ->label('Problem Description')
                    //     ->content(fn ($record) => $record?->prob_desc)
                    //     ->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(5),
        ]);
}

    public static function shouldRegisterNavigation(): bool
    {
    return in_array(Auth::check() && Auth::user()?->role, ['Admin','Safety']); // ซ่อนเมนูจาก sidebar
    }


}
