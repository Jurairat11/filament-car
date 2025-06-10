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

class Hazard_source_table extends BaseWidget
{
    protected static ?string $heading = 'Hazard Source';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 3;

    protected function getTableHeading(): ?string
    {
        $counts = Car_report::query()
            ->selectRaw('hazard_source_id as id, hazard_source_id, COUNT(*) as count')
                    ->with('hazardSource')
                    ->groupBy('hazard_source_id')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get();

        $total = $counts->sum('count');

        return "Hazard Source Total: {$total}";
    }

    public function table(Table $table): Table
    {
        return $table
        ->paginated(false)
            ->emptyStateHeading('No data available')
            ->emptyStateDescription('No data available for this hazard source.')
            ->emptyStateIcon('heroicon-o-bookmark')
            ->query(
                Car_report::query()
                    ->selectRaw('hazard_source_id as id, hazard_source_id, COUNT(*) as count')
                    ->with('hazardSource')
                    ->groupBy('hazard_source_id')
                    ->orderByDesc('count')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('hazardSource.source_name')
                    ->label('Hazard Source'),
                TextColumn::make('count')
                    ->label('Count')
            ])
            ->filters([
                Filter::make('car_reports.created_at')
                ->form([
                    DatePicker::make('created_from')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                    DatePicker::make('created_until')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('car_reports.created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('car_reports.created_at', '<=', $date),
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
        // $user = Auth::user();
        // return in_array($user?->name, ['Admin','Safety']);
    }
}
