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
                    TextEntry::make('prob_id'),

                ])
                ->collapsed()
                ->columns(5),
        ]);
}

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return Auth::check() && Auth::user()?->role === 'Safety'; // ซ่อนเมนูจาก sidebar
    // }


}
