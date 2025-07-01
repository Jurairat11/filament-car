<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class ViewCarReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.view-car-report';

    use InteractsWithRecord;

    // public function mount(int | string $record): void
    // {
    //     $this->record = $this->problem($record);
    // }



}

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return Auth::check() && Auth::user()?->role === 'Safety'; // ซ่อนเมนูจาก sidebar
    // }

