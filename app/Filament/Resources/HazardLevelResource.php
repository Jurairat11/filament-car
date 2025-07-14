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
    protected static ?string $navigationGroup = 'CAR Report';
    protected static ?string $navigationLabel = 'ระดับความอันตราย';
    protected static ?string $pluralModelLabel = 'ระดับความอันตราย';
    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ระดับความอันตราย')
                    ->description('Define the hazard level details for the car report.')
                ->schema([
                    TextInput::make('level_name')
                        ->label('ระดับความอันตราย'),
                    TextInput::make('level_desc')
                        ->label('รายละเอียดเพิ่มเติม'),
                    TextInput::make('due_days')
                        ->label('จำนวนวันครบกำหนดแก้ไข')
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
                    ->label('ระดับความอันตราย'),
                TextColumn::make('level_desc')
                    ->label('รายละเอียดเพิ่มเติม'),
                TextColumn::make('due_days')
                    ->label('จำนวนวันครบกำหนดแก้ไข')
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
