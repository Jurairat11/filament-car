<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;

class overviewTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Overview';
    protected static ?int $sort = 7;
    protected static bool $isLazy = false;
    public function table(Table $table): Table
    {
        return $table
        ->paginated(false)
            ->emptyStateHeading('No data available')
            ->emptyStateDescription('No data available for this table.')
            ->emptyStateIcon('heroicon-o-bookmark')
            ->query(
                Car_report::query()
                    ->leftJoin('car_responses', 'car_reports.id', '=','car_responses.car_id')
                    ->select('car_reports.*', 'car_responses.temp_desc', 'car_responses.temp_status', 'car_responses.perm_desc', 'car_responses.perm_status','car_responses.status')
            )
            ->columns([
                TextColumn::make('car_no')
                    ->label('Car No.')
                    ->searchable(),

                TextColumn::make('hazardLevel.level_name')
                    ->label('Hazard Level'),

                TextColumn::make('place.place_name')
                    ->label('Place'),

                TextColumn::make('car_desc')
                    ->label('Hazard Description')
                    ->limit(50),

                TextColumn::make('temp_desc')
                    ->label('Temporary C/M')
                    ->limit(20),

                TextColumn::make('temp_status')
                    ->label('Temp. Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'on_process' => 'warning',
                    'finished' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on_process' => 'on process',
                        'finished' => 'finished',
                        default => ucfirst($state),
                    }),
                TextColumn::make('perm_desc')
                    ->label('Permanent C/M')
                    ->limit(20),

                TextColumn::make('perm_status')
                    ->label('Perm. Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'on_process' => 'warning',
                    'finished' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on_process' => 'on process',
                        'finished' => 'finished',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status_delay')
                    ->label('Reply status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on_process' => 'warning',
                        'finished' => 'success',
                        'delay' => 'danger',
                        'none' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on_process' => 'on process',
                        'finished' => 'finished',
                        'delay' => 'delay',
                        'none' => 'none',
                        default => ucfirst($state),
                    }),
                ])
            ->filters([
                SelectFilter::make('status')
                ->label('Reply status')
                ->options([
                    'in_progress' => 'In Progress',
                    'draft'=> 'Draft',
                    'pending_review' => 'Pending Review',
                    'reopened' => 'Reopened',
                    'closed' => 'Closed',
                ])->indicator('Status'),
            ]);
    }

    // protected function getTableFilters(): array
    // {
    //     return [

    //         SelectFilter::make('hazard_level_id')
    //             ->label('Rank')
    //             ->relationship('hazardLevel', 'level_name')
    //             ->preload()
    //             ->indicator('Rank'),
    //             //->default(request('hazard_type_id')),

    //     ];

    // }
}
