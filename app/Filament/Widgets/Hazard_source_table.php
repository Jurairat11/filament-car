<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Car_report;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Hazard_source_table extends BaseWidget
{
    protected static ?string $heading = 'Hazard Source';
    protected static ?int $sort = 5;
    protected static bool $isLazy = false;

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
            ]);
    }
}
