<?php

namespace App\Filament\Widgets;

use App\Models\Car_report;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;

class Hazard_type_table extends BaseWidget
{
    protected static ?string $heading = 'Stop type';
    protected static ?int $sort = 6;
    protected static bool $isLazy = false;
    protected function getTableHeading(): ?string
    {
        $counts = Car_report::query()
            ->selectRaw('hazard_type_id as id, hazard_type_id, COUNT(*) as count')
            ->with('hazardType')
            ->groupBy('hazard_type_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $total = $counts->sum('count');

        return "Stop type Total: {$total}";
    }
    public function table(Table $table): Table
    {
        return $table
        ->paginated(false)
            ->emptyStateHeading('No data available')
            ->emptyStateDescription('No data available for this stop type.')
            ->emptyStateIcon('heroicon-o-bookmark')

            ->query(
                Car_report::query()
                ->selectRaw('hazard_type_id as id, hazard_type_id, COUNT(*) as count')
                ->with('hazardType')
                ->groupBy('hazard_type_id')
                ->orderByDesc('count')
                ->limit(5)
            )
            ->columns([
                TextColumn::make('hazardType.type_name')
                    ->label('Hazard Type'),
                TextColumn::make('count')
                    ->label('Count')
            ])
            ->actions([

                ViewAction::make()
                ->label('View')
                ->url(fn (Car_report $record): string =>
                    route('filament.admin.pages.department-car-alert', [
                        'hazard_type_id' => $record->hazard_type_id
                    ])
                )
                ->icon('heroicon-m-eye')
                ->color('primary')
                ->openUrlInNewTab()
                ->action(function (Car_report $record) {
                    $this->redirect(
                        route('filament.admin.pages.department-car-alert', [
                            'hazard_type_id' => $record->hazard_type_id
                        ])
                    );
                }),
            ]);
    }

}
