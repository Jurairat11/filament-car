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

class Hazard_rank_a extends BaseWidget
{
    protected static ?string $heading = 'Rank A';
    //protected static ?string $description = 'Fatal Death';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    protected function getTableHeading(): ?string
    {
        $counts = Car_report::query()
            ->where('hazard_level_id', '1')
            ->selectRaw('status_delay, COUNT(*) as count')
            ->groupBy('status_delay')
            ->pluck('count', 'status_delay')
            ->toArray();

        $total = array_sum($counts);

        return "Rank A Total: {$total}";
    }

    public function table(Table $table): Table
{

    // Group by status_delay and count
        // Car_report::query()
        // ->where('hazard_level_id', '1')
        // ->selectRaw('status_delay, COUNT(*) as count')
        // ->groupBy('status_delay')
        // ->get();

    return $table
        ->paginated(false)
        ->emptyStateHeading('No data available')
        ->emptyStateDescription('No data available for this rank.')
        ->emptyStateIcon('heroicon-o-bookmark')
        ->query(
            Car_report::query()
                    ->selectRaw('MIN(id) as id, status_delay, COUNT(*) as count')
                    ->where('hazard_level_id', '1')
                    ->groupBy('status_delay')
        )

        ->columns([
            TextColumn::make('status_delay')
                ->label('Status')
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'on_process' => 'On process',
                    'finished' => 'Finished',
                    'delay' => 'Delay',
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
