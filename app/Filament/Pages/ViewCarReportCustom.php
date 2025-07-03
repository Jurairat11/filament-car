<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Car_report;
use Filament\Forms\Components\View;
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
            // ->description(fn ($livewire) =>
            //         'CAR No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
            //     )
            ->schema([
                Placeholder::make('car_no')
                    ->label('Car No.')
                    ->content(fn () => $this->car_report->car_no ?? '-'),

                Placeholder::make('dept_id')
                    ->label('Department')
                    ->content(fn () => optional ($this->car_report->department)->dept_name ),

                Placeholder::make('sec_id')
                    ->label('Section')
                    ->content(fn()=>optional($this->car_report->section)->sec_name),

                Placeholder::make('car_date')
                    ->label('Create Date')
                    ->content(fn()=>Carbon::parse($this->car_report->car_date)->format('d/m/Y') ?? '-'),

                Placeholder::make('car_due_date')
                    ->label('Due date')
                    ->content(fn()=>Carbon::parse($this->car_report->car_due_date)->format('d/m/Y') ?? '-'),

                Placeholder::make('hazard_source_id')
                        ->label('Hazard source')
                        ->content(fn () => optional ($this->car_report->hazardSource)->source_name),

                    Placeholder::make('place_id')
                        ->label('Place')
                        ->content(fn () => optional ($this->car_report->Place)->place_name),

                    Placeholder::make('equipment')
                        ->label('Equipment')
                        ->content(fn()=>$this->car_report->equipment),

                    Placeholder::make('responsible_dept_id')
                        ->label('Reported to')
                        ->content(fn () => optional ($this->car_report->responsible)->dept_name ),

                    Placeholder::make('car_desc')
                        ->label('Description')
                        ->columnSpan(2)
                        ->content(fn()=>$this->car_report->car_desc),

                    Placeholder::make('hazard_level_id')
                        ->label('Hazard level')
                        ->content(fn () => optional ($this->car_report->hazardLevel)->level_name ),

                    Placeholder::make('hazard_type_id')
                        ->label('Hazard type')
                        ->content(fn () => optional ($this->car_report->hazardType)->type_name ),

                    View::make('components.car-reports-view-image')
                        ->label('Before Image')
                        ->viewData([
                            'path' => $this->getRecord()->img_before_path,
                        ])->columnSpan(2),

                    Placeholder::make('reopen_car_reason')
                        ->label('Cause for reopening CAR')
                        ->content(fn () => $this->car_report->reopen_car_reason ?? '-')
                        ->visible(fn () => $this->car_report->status === 'reopened')
                        ->columns(2),


            ])->collapsed(),

        ]);

    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()?->role === 'Safety'; // ซ่อนเมนูจาก sidebar
    }
}



