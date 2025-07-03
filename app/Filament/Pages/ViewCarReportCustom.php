<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Car_report;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;

class ViewCarReportCustom extends Page implements HasForms
{

    use InteractsWithForms;
    public ?array $data = [];
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.view-car-report-custom';
    protected static ?string $title = 'View Car Report';
    protected static ?string $slug = 'view-car-report-custom/{record}';

    public Car_report $car_report;

    // public function mount($record): void
    // {
    //     $this->car_report = Car_report::findOrFail($record);
    // }

    public function mount(int | string $record): void
    {
        $this->car_report = Car_report::findOrFail($record);
    }

    public function form( Form $form): Form {
        return $form->schema([
            Section::make('CAR Information')
            ->description(fn ($livewire) =>
                    'CAR No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
                )
            ->schema([
                // Placeholder::make('car_no')
                //     ->label('Car No.')
                //     ->content(fn () => $this->car_report->car_no ?? '-'),

                Placeholder::make('car_date')
                    ->label('Create Date')
                    ->content(fn()=>Carbon::parse($this->car_report->car_date)->format('d/m/Y') ?? '-'),

                Placeholder::make('car_due_date')
                    ->label('Due date')
                    ->content(fn()=>Carbon::parse($this->car_report->car_due_date)->format('d/m/Y') ?? '-'),,

                Placeholder::make('d')

                // Placeholder::make('status')
                //     ->label('Status')
                //     ->content(fn () => $this->car_report->status ?? '-'),

            ])->collapsed(),

        ]);

    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()?->role === 'Safety'; // ซ่อนเมนูจาก sidebar
    }
}



