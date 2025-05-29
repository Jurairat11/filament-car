<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardSourceResource\Pages;
use App\Filament\Resources\HazardSourceResource\RelationManagers;
use App\Models\Hazard_source;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HazardSourceResource extends Resource
{
    protected static ?string $model = Hazard_source::class;
    protected static ?string $navigationGroup = 'Car Report';
    protected static ?string $navigationLabel = 'Hazard Source';
    protected static ?string $pluralModelLabel = 'Hazard Source';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('source_name')
                    ->required()
                    ->label('Source name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('source_name')
                    ->label('Source name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHazardSources::route('/'),
            'create' => Pages\CreateHazardSource::route('/create'),
            'edit' => Pages\EditHazardSource::route('/{record}/edit'),
        ];
    }
}
