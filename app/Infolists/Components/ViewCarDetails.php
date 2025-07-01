<?php

namespace App\Infolists\Components;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\TextEntry;

class ViewCarDetails extends Component
{
    protected string $view = 'infolists.components.view-car-details';

    public static function make(): static
    {
        return app(static::class);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Details')
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('body')
                            ->markdown()
                            ->extraAttributes(['class' => 'prose'])
                    ])
            ]);
    }
}
