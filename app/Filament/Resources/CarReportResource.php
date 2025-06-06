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
    protected static ?string $navigationLabel = 'Create Car';
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
                    Select::make('problem_id')
                    ->label('Problem ID')
                    ->options(fn() => Problem::all()->pluck('prob_id', 'id'))
                    ->required(),

                    // Select::make('created_by')
                    // ->label('Created by')
                    // ->options(fn () => User::where('dept_id',Auth::user()?->dept_id)->pluck('emp_id', 'id'))
                    // ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                    // ->searchable()
                    // ->preload()
                    // ->required(),

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
                    ->required(),

                    Select::make('sec_id')
                    ->label('Section')
                    ->relationship('section','sec_name')
                    ->searchable()
                    ->preload()
                    ->required(),

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


                    Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'reported' => 'Reported',
                        'in_progress' => 'In progress',
                        'pending_review' => 'Pending review',
                        'reopened' => 'Reopened',
                        'closed' => 'Closed'
                    ])
                    ->default('draft'),

                    // Select::make('status_delay')
                    // ->label('Status')
                    // ->options([
                    //     'on_process' => 'On process',
                    //     'finished' => 'Finished',
                    //     'delay' => 'Delay',
                    //     'none' => 'None'
                    // ])->default('none'),

                    Hidden::make('parent_car_id')
                    ->dehydrated(true)
                    ->default(request()->get('parent_car_id')),

                    Hidden::make('followed_car_id')
                    ->dehydrated(true)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    'pending_review' => 'warning',
                    'reopened' => 'warning',
                    'closed' => 'success',
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
                ->label('Reported to')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('users.FullName')
                ->label('Created by'),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->dateTime('d/m/Y H:i')
                //->dateTimeTooltip()
                ->timezone('Asia/Bangkok'),

                TextColumn::make('remaining_days')
                ->label('Remaining')
                ->getStateUsing(function ($record) {
                    // ถ้าสถานะเป็น pending_review หรือ closed ให้แสดงว่า Replied
                    if (in_array($record->status, ['pending_review', 'closed'])) {
                        return 'Replied';
                    }

                    // ถ้าสถานะเป็น draft ให้คืนค่าที่เคยคำนวณไว้ก่อนหน้านี้
                    if ($record->status === 'draft') {
                        return $record->remaining_days ?? ''; // หรือใช้ฟิลด์อื่นที่เก็บค่าไว้
                    }

                    // คำนวณ remaining days สำหรับ reported หรือสถานะอื่น ๆ
                    $carDate = Carbon::parse($record->car_date);
                    $dueDate = $carDate->addDays($record->car_delay ?? 0);
                    $remaining = (int) round(now()->floatDiffInDays($dueDate, false));
                    $unit = abs($remaining) === 1 ? 'day' : 'days';

                    // ถ้าเหลือ -1 วัน และยังไม่ได้ตั้งค่า delay ให้ตั้งค่า
                    if ($remaining === -1 && $record->status_delay !== 'delay') {
                        $record->status_delay = 'delay';
                        $record->save();
                    }

                    return "{$remaining} {$unit}";
                })
                ->color(function ($record) {
                    if (in_array($record->status, ['pending_review', 'closed'])) {
                        return 'info';
                    }

                    if ($record->status === 'draft') {
                        return null;
                    }

                    return now()->gt(Carbon::parse($record->car_date)->addDays($record->car_delay)) ? 'danger' : 'success';
                }),


                // TextColumn::make('status_delay')
                // ->label('Reply status')
                // ->badge()
                // ->color(fn (string $state): string => match ($state) {
                //     'on_process' => 'warning',
                //     'finished' => 'success',
                //     'delay' => 'danger',
                //     'none' => 'gray'
                // })
                // ->formatStateUsing(fn (string $state) => match ($state) {
                //     'on_process' => 'on process',
                //     'finished' => 'finished',
                //     'delay' => 'delay',
                //     'none' => 'none',
                //     default => ucfirst($state),
                // })->default('none'),

            ])
            ->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
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

    // public static function getNavigationBadge(): ?string
    // {
    //     if (User::role('Safety')) {
    //         return Car_report::where('status', 'pending_review')
    //         ->count();
    //     }

    //     return null; // Ensure a value is returned in all paths
    // }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     $user = Auth::user();
    //     return in_array($user?->name, ['Admin','Safety']);
    // }

}
