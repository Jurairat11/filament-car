<?php

namespace App\Filament\Pages;

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
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('dd-mm-yyyy')
                    ->closeOnDateSelection()
                    ->suffixAction(
                    Action::make('resetDate')
                        ->icon('heroicon-o-x-circle')
                        ->tooltip('clear')
                        ->action(fn ($state, callable $set) => $set('startDate', null))
                    ),
                DatePicker::make('endDate')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('dd-mm-yyyy')
                    ->closeOnDateSelection()
                    ->suffixAction(
                    Action::make('resetDate')
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

}
