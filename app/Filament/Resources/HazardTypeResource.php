<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HazardTypeResource\Pages;
use App\Filament\Resources\HazardTypeResource\RelationManagers;
use App\Models\Hazard_type;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HazardTypeResource extends Resource
{
    protected static ?string $model = Hazard_type::class;
    protected static ?string $navigationGroup = 'CAR Report';
    protected static ?string $navigationLabel = 'ประเภทของอันตราย';
    protected static ?string $pluralModelLabel = 'ประเภทของอันตราย';
    protected static ?string $navigationIcon = 'heroicon-o-stop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('type_name')
                    ->label('ประเภทของอันตราย')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('type_name')
                    ->label('ประเภทของอันตราย'),
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
            'index' => Pages\ListHazardTypes::route('/'),
            'create' => Pages\CreateHazardType::route('/create'),
            'edit' => Pages\EditHazardType::route('/{record}/edit'),
        ];
    }

}
