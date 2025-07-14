<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Hazard_rank_a;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;
    public ?Model $record = null;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Select::make('dept_id')
                    ->label('Department')
                    ->options(Department::all()->pluck('dept_name', 'dept_id'))
                    ->searchable()
                    ->preload()
                    ->hidden(function () {
                    return Auth::user()->hasRole('User');
                }),
                DatePicker::make('startDate')
                    ->label('Created from')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('dd-mm-yyyy')
                    ->closeOnDateSelection()
                    ->suffixAction(
                    Action::make('resetStartDate')
                        ->icon('heroicon-o-x-circle')
                        ->tooltip('clear')
                        ->action(fn ($state, callable $set) => $set('startDate', null))
                        // ->action(function ($state, callable $set) {
                        //     $set('startDate', null);
                        //     return redirect()->route('filament.admin.pages.dashboard');
                        //     })


                    ),

                DatePicker::make('endDate')
                    ->label('Created until')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('dd-mm-yyyy')
                    ->closeOnDateSelection()
                    ->suffixAction(
                    Action::make('resetEndDate')
                        ->icon('heroicon-o-x-circle')
                        ->tooltip('clear')
                        ->action(fn ($state, callable $set) => $set('endDate', null))
                    ),
                ])->columns(3)

        ]);


    }

    public function getColumns(): int | string | array
    {
        return 6;
    }
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar-square'; // Replace with your desired icon
    }

    public function getTitle(): string
    {
        $date = now()->format('d/m/Y');
        return "Hazard Identification and Countermeasure Status Dashboard as of {$date}";
    }



}
