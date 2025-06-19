<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\User;
use Filament\Tables;
use App\Models\Problem;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Car_report;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Layout;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CarReportResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CarReportResource\RelationManagers;

class CarReportResource extends Resource
{
    protected static ?string $model = Car_report::class;
    protected static ?string $navigationGroup = 'Car Report';
    protected static ?string $navigationLabel = 'Create CAR';
    protected static ?string $pluralModelLabel = 'Car Report';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('CAR')->id('submit')
                ->description(fn ($livewire) =>
                    'No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
                )
                ->schema([
                    // Select::make('problem_id')
                    // ->label('Problem ID')
                    // ->options(fn() => Problem::where('status','!=','closed')->pluck('prob_id', 'id'))
                    // ->required(),

                    Select::make('problem_id')
                    ->label('Problem ID')
                    ->relationship('problem', 'prob_id')
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prob_id} ({$record->status})"),

                    Hidden::make('created_by')
                    ->default(Auth::user()?->id)
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                    Select::make('dept_id')
                    ->label('Department')
                    ->relationship('department','dept_name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required(),

                    Select::make('sec_id')
                    ->label('Section')
                    ->relationship('section','sec_name')
                    ->searchable()
                    ->preload()
                    ->required(), // รองรับการโหลด option ใหม่

                    DatePicker::make('car_date')
                    ->label('Create date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                    DatePicker::make('car_due_date')
                    ->label('Car due date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->required(),

            ])->columns(5),

            Section::make('Hazard Details')
            ->schema([
                Split::make([
                Section::make()
                    ->schema([
                        Select::make('hazard_source_id')
                            ->label('Hazard source')
                            ->relationship('hazardSource','source_name')
                            ->required(),

                        Select::make('place_id')
                            ->label('Place')
                            ->relationship('place','place_name')
                            ->createOptionForm([
                                TextInput::make('place_name')
                                    ->label('Place name')
                            ])
                            ->required(),

                        TextInput::make('equipment')
                            ->label('Machine/Equipment')
                            ->required(),

                        Select::make('hazard_level_id')
                            ->label('Hazard level')
                            ->relationship('hazardLevel','level_name')
                            ->required(),

                        Select::make('hazard_type_id')
                            ->label('Hazard type')
                            ->relationship('hazardType','type_name')
                            ->createOptionForm([
                                TextInput::make('type_name')
                                    ->label('Hazard type')
                            ])
                            ->required(),

                    ]),
                Section::make()
                    ->schema([
                        Textarea::make('car_desc')
                            ->label('Description')
                            ->autosize()
                            ->required(),

                        FileUpload::make('img_before')
                            ->label('Picture before')
                            ->image()
                            ->required()
                            ->directory('form-attachments')
                            ->visibility('public'),

                        Select::make('responsible_dept_id')
                            ->label('Reported to')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->relationship('responsible','dept_name'),

                        ]),
                    ])->columns(1)->columnSpan(2),
                ]),

                    Hidden::make('parent_car_id')
                    ->dehydrated(true)
                    ->default(request()->get('parent_car_id')),

                    Hidden::make('followed_car_id')
                    ->dehydrated(true),

                    Hidden::make('status')
                    ->default('accepted')
                    ->dehydrated(true)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') //sort order by created_at
            ->columns([
                TextColumn::make('car_no')
                ->label('Car No.')
                ->searchable(),

                TextColumn::make('car_date')
                ->label('Create date')
                ->sortable()
                ->dateTime('d/m/Y')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('car_due_date')
                ->label('Due date')
                ->sortable()
                ->dateTime('d/m/Y'),

                ImageColumn::make('img_before')
                ->label('Picture before'),

                TextColumn::make('hazardLevel.level_name')
                ->label('Hazard level')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('hazardType.type_name')
                ->label('Hazard type')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'reported' => 'info',
                    'in_progress' => 'warning',
                    'pending_review' => 'success',
                    'reopened' => 'warning',
                    'closed' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'draft' => 'draft',
                    'reported' => 'reported',
                    'in_progress' => 'in progress',
                    'pending_review' => 'pending review',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),
                TextColumn::make('close_car_date')
                ->label('Close date')
                ->dateTime('d/m/Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('responsible.dept_name')
                ->label('Reported to'),

                TextColumn::make('users.FullName')
                ->label('Created by'),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->dateTime('d/m/Y H:i')
                //->dateTimeTooltip()
                ->timezone('Asia/Bangkok'),

                TextColumn::make('follow_car')
                ->label('Reopen car')
                ->getStateUsing(function ($record) {
                    return $record->status === 'reopened' ? optional($record->followUp)->car_no : null;
                })
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                    ->closeOnDateSelection(),
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
                }),

                SelectFilter::make('responsible_id')
                ->label('Department')
                ->relationship('responsible', 'dept_name')
                ->searchable()
                ->preload()
                ->indicator('Department'),

                // SelectFilter::make('hazard_type_id')
                // ->label('Stop type')
                // ->relationship('hazardType', 'type_name')
                // ->preload()
                // ->indicator('Stop type'),

                SelectFilter::make('hazard_level_id')
                ->label('Hazard level')
                ->relationship('hazardLevel', 'level_name')
                ->preload()
                ->indicator('Hazard level'),

                SelectFilter::make('status_delay')
                ->label('Reply status')
                ->options([
                    'on_process' => 'On process',
                    'finished' => 'Finished',
                    'delay' => 'Delay',
                ])->indicator('status'),

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListCarReports::route('/'),
            'create' => Pages\CreateCarReport::route('/create'),
            'view' => Pages\ViewCarReport::route('/{record}'),
            'edit' => Pages\EditCarReport::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
        {

            if (Auth::user()->hasRole('Safety')) {
            return (string) static::$model::where('status', 'pending_review')->count();
            }
            return null;

        }

    public static function getNavigationBadgeTooltip(): ?string
        {
            return 'Pending review CAR';
        }


}
