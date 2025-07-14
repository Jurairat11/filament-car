<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Place;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\PlaceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PlaceResource\RelationManagers;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;
    protected static ?string $navigationGroup = 'CAR Report';
    protected static ?string $navigationLabel = 'สถานที่';
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('place_name')
                    ->required()
                    ->label('สถานที่ที่พบอันตราย'),
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
                TextColumn::make('place_name')
                    ->label('สถานที่ที่พบอันตราย')
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
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
