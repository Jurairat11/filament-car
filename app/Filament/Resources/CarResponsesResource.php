<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Car_report;
use Filament\Tables\Table;
use App\Models\Car_responses;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
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

    public static function getEloquentQuery(): Builder
    {
        // If user has 'Safety' role, show all records
        if (Auth::user()?->hasAnyRole(['Safety','Admin']))  {
            return parent::getEloquentQuery();
        }

        // Otherwise, filter by responsible_dept_id
        return parent::getEloquentQuery()
            ->whereHas('carReport', function ($query) {
                $query->where('responsible_dept_id', Auth::user()->dept_id);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('CAR Responses')
                ->schema([

                Split::make([
                Section::make()
                ->schema([
                    Select::make('car_id')
                        ->label('CAR No.')
                        ->options(function (){
                                $responsibleDept = Auth::user()->dept_id;
                                return Car_report::where('responsible_dept_id',$responsibleDept)
                                ->get()
                                ->mapWithKeys(fn ($car) => [$car->id => "{$car->car_no} ({$car->status})"]);
                            })
                            ->searchable()
                            ->preload()
                            ->default(fn($record)=> $record?->car_id)

                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set){
                                $car = Car_report::find($state);
                                if($car){
                                    $set('temp_due_date', $car->car_date);
                                    $set('perm_due_date', $car->car_due_date);
                                }
                        }),

                        Hidden::make('status')
                        ->default('draft')
                        ->dehydrated(true),

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

                    ]),

                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Temporary action')
                        ->schema([
                            Textarea::make('temp_desc')
                            ->label('Temporary action')
                            ->autosize(),

                            DatePicker::make('temp_due_date')
                            ->label('Due date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(),

                            Select::make('temp_responsible_id')
                            ->label('Responsible')
                            ->searchable()
                            ->relationship('tempResponsible','emp_id',fn()=> User::where('dept_id',Auth::user()?->dept_id))
                            // ->options(fn () => User::where('dept_id',Auth::user()?->dept_id)->pluck('emp_id', 'id'))
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                            ->preload(),

                        ]),
                    Tabs\Tab::make('Permanent action')
                        ->schema([
                            Textarea::make('perm_desc')
                            ->label('Permanent action')
                            ->autosize(),

                            DatePicker::make('perm_due_date')
                            ->label('Due date')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(true),

                            //->getSearchResultsUsing(fn (string $search) => User::where('dept_id', Auth::user()->dept_id)->pluck('emp_id', 'emp_name')->toArray()),

                            Select::make('perm_responsible_id')
                            ->label('Responsible')
                            ->searchable()
                            ->relationship('permResponsible','emp_id',fn()=> User::where('dept_id',Auth::user()?->dept_id))
                            // ->options(fn () => User::where('dept_id',Auth::user()?->dept_id)->pluck('emp_id', 'id'))
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                            ->preload(),

                            Textarea::make('preventive')
                            ->label('Preventive action')
                            ->autosize()
                            ->nullable()

                        ]),
                    ]),
                ]),
            ])->columns(1)->columnSpan(2),

                Hidden::make('created_by')
                ->label('Created by')
                //->options(fn () => User::where('dept_id',Auth::user()?->dept_id)->pluck('emp_id', 'id'))
                ->default(Auth::user()?->id)
                ->disabled()
                ->dehydrated()
                ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at','desc')
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
                    'draft' => 'gray',
                    'pending_review' => 'success',
                    'reopened' => 'warning',
                    'closed' => 'gray'
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'draft' => 'draft',
                    'pending_review' => 'pending review',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),

                TextColumn::make('createdResponse.FullName')
                ->label('Created by')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->timezone('Asia/Bangkok')
                ->dateTime('d/m/Y H:i')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('temp_desc')
                ->label('Temporary action')
                ->limit(50)
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('temp_due_date')
                ->label('Temp due date')
                ->timezone('Asia/Bangkok')
                ->dateTime('d/m/Y'),

                TextColumn::make('temp_status')
                    ->label('Temp status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on process' => 'warning',
                        'finished' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on process' => 'on process',
                        'finished' => 'finished',
                        default => ucfirst($state),
                    }),

                TextColumn::make('perm_desc')
                    ->label('Permanent action')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('perm_due_date')
                    ->label('Perm due date')
                    ->timezone('Asia/Bangkok')
                    ->dateTime('d/m/Y'),

                TextColumn::make('days_perm')
                    ->label('Days Perm'),

                TextColumn::make('perm_status')
                    ->label('Perm status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on process' => 'warning',
                        'finished' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on process' => 'on process',
                        'finished' => 'finished',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status_reply')
                    ->label('Status reply')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on process' => 'warning',
                        'finished' => 'success',
                        'delay' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on process' => 'on process',
                        'finished' => 'finished',
                        'delay' => 'delay',
                        default => ucfirst($state),
                    })

            ])
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'pending_review' => 'Pending review',
                    'reopened' => 'Reopened',
                    'closed' => 'Closed'
                ])->indicator('status'),

                SelectFilter::make('status_reply')
                ->options([
                    'on process' => 'On process',
                    'finished' => 'Finished',
                    'delay' => 'Delay',
                ])->indicator('Status reply')->default(request('status_reply')),

                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('dd-mm-yyyy')
                        ->closeOnDateSelection(),
                    DatePicker::make('created_until')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('dd-mm-yyyy')
                        ->closeOnDateSelection()
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
                })->columnSpan(2)->columns(2)
            ],layout: FiltersLayout::AboveContent)->filtersFormColumns(4)

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions')->dropdownPlacement('top-start'),

                // ActionGroup::make([
                //     Tables\Actions\ViewAction::make(),
                //     Tables\Actions\EditAction::make(),
                //     Tables\Actions\DeleteAction::make(),
                // ])
                //     ->label('More actions')
                //     ->icon('heroicon-m-ellipsis-vertical')
                //     ->size(ActionSize::Small)
                //     ->color('primary')
                //     ->button()
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
