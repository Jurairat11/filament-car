<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Car_report;
use Filament\Tables\Table;
use App\Models\Car_responses;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CarResponsesResource\Pages;
use App\Filament\Resources\CarResponsesResource\RelationManagers;

class CarResponsesResource extends Resource
{
    protected static ?string $model = Car_responses::class;
    protected static ?string $navigationLabel = 'CAR Responses';
    protected static ?string $pluralModelLabel = 'Car Responses';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Car Responses';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('CAR Responses')
                ->schema([

                Select::make('car_id')
                ->label('CAR No.')
                ->options(function() {
                $deptId = Auth::user()?->dept_id;
                return Car_report::where('responsible_dept_id', $deptId)
                    ->where('status', 'in_progress')
                    ->pluck('car_no', 'id');
                }),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'accepted' => 'Accepted',
                        'reported' => 'Reported',
                        'in_progress' => 'In progress',
                        'dismissed' => 'Dismissed',
                        'pending_review' => 'Pending review',
                        'reopened' => 'Reopened',
                        'closed' => 'Closed'
                    ])
                    ->default('in_progress'),

                Textarea::make('cause')
                ->label('Cause')
                ->autosize()
                ->required(),

                FileUpload::make('img_after')
                    ->label('Picture after')
                    ->image()
                    ->required()
                    ->directory('form-attachments')
                    ->visibility('public'),
                ])->columns(2),

                Section::make('Action')
                ->schema([

                Textarea::make('temp_desc')
                ->label('Temporary action')
                ->autosize(),

                Textarea::make('perm_desc')
                ->label('Permanent action')
                ->autosize(),

                DatePicker::make('temp_due_date')
                ->label('Due date')
                ->native(false)
                ->displayFormat('d/m/Y'),

                DatePicker::make('perm_due_date')
                ->label('Due date')
                ->native(false)
                ->displayFormat('d/m/Y'),

                Select::make('temp_responsible_id')
                ->label('Responsible')
                ->searchable()
                ->relationship('tempResponsible','emp_id')
                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                ->preload(),
                //->getSearchResultsUsing(fn (string $search) => User::where('dept_id', Auth::user()->dept_id)->pluck('emp_id', 'emp_name')->toArray()),

                Select::make('perm_responsible_id')
                ->label('Responsible')
                ->searchable()
                ->relationship('permResponsible','emp_id')
                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                ->preload(),

                Textarea::make('preventive')
                ->label('Preventive action')
                ->autosize()
                ->required(),

                Select::make('created_by')
                ->label('Created by')
                ->relationship('createdResponse','emp_id')
                ->searchable()
                ->preload()
                ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                ->default(function () {
                    $user = Auth::user();
                    return $user ? $user->id : null;
                })
                ->required()
                ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('carReport.car_no')
                ->label('CAR no.')
                ->searchable(),
                ImageColumn::make('img_after')
                ->label('Picture after'),
                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'info',
                    'accepted' => 'success',
                    'reported' => 'info',
                    'in_progress' => 'warning',
                    'pending_review' => 'success',
                    'reopened' => 'warning',
                    'closed' => 'gray'
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'new' => 'new',
                    'reported' => 'reported',
                    'in_progress' => 'in progress',
                    'pending_review' => 'pending review',
                    'dismissed' => 'dismissed',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),
                TextColumn::make('createdResponse.FullName')
                ->label('Created by'),
                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->timezone('Asia/Bangkok')
                ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')->displayFormat('d/m/Y')->native(false),
                    DatePicker::make('created_until')->displayFormat('d/m/Y')->native(false)->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCarResponses::route('/'),
            'create' => Pages\CreateCarResponses::route('/create'),
            'view' => Pages\ViewCarResponses::route('/{record}'),
            'edit' => Pages\EditCarResponses::route('/{record}/edit'),
        ];
    }

}
