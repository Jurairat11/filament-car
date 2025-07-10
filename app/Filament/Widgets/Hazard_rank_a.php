<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Collection;
use App\Models\Car_responses;

class Hazard_rank_a extends Widget
{
    protected static string $view = 'filament.widgets.hazard-rank-a';

    public function getTableData(): Collection
    {
        $finished = Car_responses::where('status_reply', 'finished')->count();
        $onProcess = Car_responses::where('status_reply', 'on process')->count();
        $delay = Car_responses::where('status_reply', 'delay')->count();
        $total = $finished + $onProcess + $delay;

        return collect([
            ['status' => 'Finished', 'count' => $finished],
            ['status' => 'On process', 'count' => $onProcess],
            ['status' => 'Delay', 'count' => $delay],
            ['status' => 'Total', 'count' => $total],
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('count')->label('Count'),
            ])
            ->records($this->getTableData());
    }
}
