<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\DatePicker;
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
                    ->preload(),
                DatePicker::make('startDate')
                ->native(false)
                // ->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
                DatePicker::make('endDate')
                ->native(false)
                //->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
                Toggle::make('active')
                ])->columns(4)
            ]);
    }
    // public function getColumns(): int | string | array
    // {
    //     return [
    //         'md' => 6,
    //         'xl' => 2,
    //     ];
    // }
    public function getColumns(): int | string | array
    {
        return 3;
    }


}
