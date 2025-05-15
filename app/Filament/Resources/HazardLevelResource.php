<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardLevelResource\Pages;
use App\Filament\Resources\HazardLevelResource\RelationManagers;
use App\Models\Hazard_level;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HazardLevelResource extends Resource
{
    protected static ?string $model = Hazard_level::class;
    protected static ?string $navigationGroup = 'Car Report';
    protected static ?string $navigationLabel = 'Hazard Level';
    protected static ?string $pluralModelLabel = 'Hazard Level';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('level_name')
                ->label('Level'),
                TextInput::make('level_desc')
                ->label('Description')

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
