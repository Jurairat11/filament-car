<?php

namespace App\Filament\Pages;

use App\Models\Car_report;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ViewCarReportCustom extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.view-car-report-custom';
    public ?Car_report $record = null;
    public function mount($record): void
    {
        $this->record = Car_report::findOrFail($record);
    }
    public function getTitle(): string
    {
        return 'View Car Report';
    }
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()?->role === 'Safety'; // ซ่อนเมนูจาก sidebar
    }
}



