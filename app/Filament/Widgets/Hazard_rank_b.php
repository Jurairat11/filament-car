<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Car_report;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class Hazard_rank_b extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'Rank B';
    protected static ?string $description = 'Injury (Disability, Loss of Organ and Absent)';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected function getTableHeading(): ?string
    {
        $counts = Car_report::query()
            ->where('hazard_level_id', '2')
            ->selectRaw('status_delay, COUNT(*) as count')
            ->groupBy('status_delay')
            ->pluck('count', 'status_delay')
            ->toArray();

        $total = array_sum($counts);

        return "Rank B Total: {$total}";
    }
    public function table(Table $table): Table
{

    return $table
        ->paginated(false)
        ->emptyStateHeading('No data available')
        ->emptyStateDescription('No data available for this rank.')
        ->emptyStateIcon('heroicon-o-bookmark')
            ->query(
            Car_report::query()
                    ->selectRaw('MIN(id) as id, status_delay, COUNT(*) as count')
                    ->where('hazard_level_id', '2')
                    ->groupBy('status_delay')
                    )
            ->columns([
                TextColumn::make('status_delay')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on_process' => 'On process',
                        'finished' => 'Finished',
                        'delay' => 'Delay',
                        'none' => 'None',
                        default => ucfirst($state)
                    }),
                TextColumn::make('count')
                    ->label('Count')

            ])->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
                SelectFilter::make('responsible_id')
                ->label('Department')
                ->relationship('responsible', 'dept_name')
                ->searchable()
                ->preload()
                ->indicator('Department'),
            ]);
            // ->actions([
            //     ViewAction::make()
            //     ->label('View')
            //     ->url(fn (Car_report $record): string =>
            //         route('filament.admin.pages.department-car-alert', [
            //             'status_delay' => $record->status_delay
            //         ])
            //     )
            //     ->icon('heroicon-m-eye')
            //     ->color('primary')
            //     ->openUrlInNewTab()
            //     ->action(function (Car_report $record) {
            //         $this->redirect(
            //             route('filament.admin.pages.department-car-alert', [
            //                 'status_delay' => $record->status_delay
            //             ])
            //         );
            //     }),
            // ]);
        }
    }
