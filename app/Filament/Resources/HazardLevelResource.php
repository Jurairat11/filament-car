<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Hazard_level;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\HazardLevelResource\Pages;
use App\Filament\Resources\HazardLevelResource\RelationManagers;

class HazardLevelResource extends Resource
{
    protected static ?string $model = Hazard_level::class;
    protected static ?string $navigationGroup = 'Car Report';
    protected static ?string $navigationLabel = 'Hazard Level';
    protected static ?string $pluralModelLabel = 'Hazard Level';
    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hazard Level')
                    ->description('Define the hazard level details for the car report.')
                ->schema([
                    TextInput::make('level_name')
                        ->label('Level')
                        ->placeholder('Enter hazard level name'),
                    TextInput::make('level_desc')
                        ->label('Description')
                        ->placeholder('Enter hazard level description'),
                    TextInput::make('due_days')
                        ->label('Due Days')
                        ->placeholder('Enter number of days for due')
                        ->numeric()
                        ->minValue(0)
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('level_name')
                    ->label('Level'),
                TextColumn::make('level_desc')
                    ->label('Description'),
                TextColumn::make('due_days')
                    ->label('Due Days')
                    ->numeric()

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
            'index' => Pages\ListHazardLevels::route('/'),
            'create' => Pages\CreateHazardLevel::route('/create'),
            'edit' => Pages\EditHazardLevel::route('/{record}/edit'),
        ];
    }

}
