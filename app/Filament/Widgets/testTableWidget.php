<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Car_report;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class testTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Rank A';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 2;
    protected static bool $isLazy = false;
    protected function getTableHeading(): ?string
    {
        $counts = Car_report::query()
            ->join('car_responses','car_responses.car_id','=','car_reports.id')
            ->where('car_reports.hazard_level_id', '1')
            ->selectRaw('car_responses.status_reply, COUNT(*) as count')
            ->groupBy('car_responses.status_reply')
            ->pluck('count', 'car_responses.status_reply')
            ->toArray();

        $total = array_sum($counts);

        return "Rank A Total: {$total}";
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
                ->fromRaw('(
                    SELECT "on process" as status_reply, COUNT(*) as count
                    FROM car_reports
                    JOIN car_responses ON car_responses.car_id = car_reports.id
                    WHERE car_reports.hazard_level_id = 1 AND car_responses.status_reply = "on process"

                    UNION ALL

                    SELECT "finished" as status_reply, COUNT(*) as count
                    FROM car_reports
                    JOIN car_responses ON car_responses.car_id = car_reports.id
                    WHERE car_reports.hazard_level_id = 1 AND car_responses.status_reply = "finished"

                    UNION ALL

                    SELECT "delay" as status_reply, COUNT(*) as count
                    FROM car_reports
                    JOIN car_responses ON car_responses.car_id = car_reports.id
                    WHERE car_reports.hazard_level_id = 1 AND car_responses.status_reply = "delay"
                ) as sub')
        )
        ->columns([
            TextColumn::make('status_reply')
                ->label('Status')
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'on process' => 'On process',
                    'finished' => 'Finished',
                    'delay' => 'Delay',
                    default => ucfirst($state)
                }),
            TextColumn::make('count')
                ->label('Count')
        ])
            ->filters([
                Filter::make('car_responses.created_at')
                    ->form([
                        DatePicker::make('created_from')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                        DatePicker::make('created_until')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('car_responses.created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('car_responses.created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from']) {
                            $indicators[] = 'from: ' . Carbon::parse($data['created_from'])->format('d/m/Y');
                        }

                        if ($data['created_until']) {
                            $indicators[] = 'until: ' . Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                SelectFilter::make('responsible_id')
                    ->label('Department')
                    ->relationship('responsible', 'dept_name')
                    ->searchable()
                    ->preload()
                    ->indicator('Department'),
            ]);
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['Safety', 'Admin']);
    }
}

