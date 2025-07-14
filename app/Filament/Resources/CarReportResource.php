<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Problem;
use App\Models\Sections;
use Filament\Forms\Form;
use App\Models\Car_report;
use App\Models\Department;
use Filament\Tables\Table;
use App\Models\Hazard_level;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
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
    protected static ?string $navigationGroup = 'CAR Report';
    protected static ?string $navigationLabel = 'สร้าง CAR';
    protected static ?string $pluralModelLabel = 'Car Report';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('CAR Information')->id('submit')
                ->description(fn ($livewire) =>
                    'No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
                )
                ->schema([
                    // Select::make('problem_id')
                    // ->label('Problem ID')
                    // ->options(fn() => Problem::where('status','!=','closed')->pluck('prob_id', 'id'))
                    // ->required(),

                    // Select::make('problem_id')
                    //     ->label('Problem ID')
                    //     ->placeholder('Select problem ID')
                    //     ->relationship('problem', 'prob_id',(fn() => Problem::where('status', '!=', 'new')))
                    //     ->preload()
                    //     ->searchable()
                    //     ->required()
                    //     ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prob_id} ({$record->status})"),

                    Select::make('problem_id')
                    ->label('Problem ID')
                    ->relationship('problem', 'prob_id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prob_id} ({$record->status})")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    // ->helperText('เลือกปัญหา (ถ้ามี)')
                    ->helperText(new HtmlString('<strong style="color:red;">*เลือกปัญหา (ถ้ามี)</strong>')),

                    Hidden::make('created_by')
                        ->default(Auth::user()?->id)
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('dept_id')
                        ->label('แผนก')
                        ->placeholder('เลือกแผนกผู้รับผิดชอบ')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(Department::pluck('dept_name', 'dept_id'))
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('sec_id', null)),

                    Select::make('sec_id')
                        ->label('ส่วนงาน')
                        ->placeholder('เลือกส่วนงานผู้รับผิดชอบ')
                        ->searchable()
                        ->preload()
                        ->options(function (callable $get) {
                            $deptId = $get('dept_id');
                            if (!$deptId) {
                                return [];
                            }
                            return Sections::where('dept_id', $deptId)->pluck('sec_name', 'sec_id');
                        })
                        ->required(),

                    DatePicker::make('car_date')
                        ->label('วันที่สร้าง CAR')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    DatePicker::make('car_due_date')
                        ->label('วันที่ครบกำหนดแก้ไข')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        // ->format('d/m/Y')
                        ->placeholder('dd/mm/yyyy')
                        ->closeOnDateSelection()
                        ->disabled()
                        ->dehydrated(),

            ])->columns(5),

            Section::make('CAR Details')
            ->schema([
                Split::make([
                Section::make()
                    ->schema([
                        Select::make('hazard_source_id')
                            ->label('Hazard source')
                            ->placeholder('Select source')
                            ->relationship('hazardSource','source_name')
                            ->required(),

                        Select::make('place_id')
                            ->label('สถานที่ที่พบอันตราย')
                            ->relationship('place','place_name')
                            ->searchable()
                            ->preload()
                            ->placeholder('เลือกสถานที่ที่พบอันตราย')
                            ->createOptionForm([
                                TextInput::make('place_name')
                                    ->label('สถานที่ที่พบอันตราย')
                            ])
                            ->required(),

                        TextInput::make('equipment')
                            ->label('เครื่องจักร/สิ่งของ')
                            ->placeholder('เครื่องจักร/สิ่งของที่เป็นสาเหตุอันตราย')
                            ->required(),

                        Select::make('hazard_level_id')
                            ->label('ระดับความอันตราย')
                            ->helperText(new HtmlString('<strong style="color:red;">*ระดับ A = 3 วัน, ระดับ B = 5 วัน และระดับ C = 7 วัน</strong>'))
                            ->placeholder('เลือกระดับความอันตราย')
                            ->relationship('hazardLevel','level_name')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Hazard_level model has a 'due_days' field
                                $hazardLevel = Hazard_level::find($state);

                                if ($hazardLevel && $hazardLevel->due_days) {
                                    $currentDate = now();
                                    $newDueDate = $currentDate->copy()->addDays($hazardLevel->due_days);
                                    $set('car_due_date', $newDueDate->format('Y/m/d'));

                                    //dd($newDueDate);
                                }

                                // $date =2024-08-08;
                                // $daysToAdd = 5;
                                // $date = $date->addDays($daysToAdd);

                                //addDays(5)
                            })
                            ->required(),

                        Select::make('hazard_type_id')
                            ->label('ประเภทของอันตราย')
                            ->placeholder('เลือกประเภทของอันตราย')
                            ->relationship('hazardType','type_name')
                            // ปิด กดเพิ่ม hazard type
                            // ->createOptionForm([
                            //     TextInput::make('type_name')
                            //         ->label('Hazard type')
                            // ])
                            ->required(),

                    ]),
                Section::make()
                    ->schema([
                        Textarea::make('car_desc')
                            ->label('รายละเอียดความไม่ปลอดภัย')
                            ->autosize()
                            ->required(),

                        FileUpload::make('img_before_path')
                            ->label('รูปภาพอันตราย (ก่อน)')
                            ->helperText('ขนาดสูงสุดของไฟล์รูปภาพไม่เกิน 5MB')
                            ->image()
                            ->downloadable()
                            //->acceptedFileTypes(['jpg'])
                            ->maxSize(5120) // 5MB
                            ->directory('form-attachments')
                            ->visibility('public')
                            ->required(),

                        Hidden::make('img_before')
                        ->dehydrated(),

                        // Select::make('responsible_dept_id')
                        //     ->label('Reported to')
                        //     ->placeholder('Select department')
                        //     ->helperText('The department responsible for the issue')
                        //     ->searchable()
                        //     ->preload()
                        //     ->required()
                        //     ->relationship('responsible','dept_name'),

                        Select::make('responsible_group')
                        ->label('กลุ่มหน่วยงาน')
                        ->options([
                            'general' => 'ทั่วไป',
                            'other' => 'อื่นๆ',

                        ])
                        ->default('general')
                        ->reactive(),

                        Select::make('responsible_dept_id')
                            ->label('เลือกหน่วยงานที่รับผิดชอบ')
                            ->options(function(callable $get) {
                                $group = $get('responsible_group');

                                if(!$group){
                                    return [];
                                }
                                return Department::where('group',$group)->pluck('dept_name','dept_id');
                            })
                            ->hidden(fn(callable $get) => blank($get('responsible_group')))
                            ->reactive()

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
                ->label('เลขที่ CAR')
                ->searchable(),

                TextColumn::make('car_date')
                ->label('วันที่สร้าง CAR')
                ->sortable()
                ->dateTime('d/m/Y')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('car_due_date')
                ->label('วันที่ครบกำหนดแก้ไข')
                ->sortable()
                ->dateTime('d/m/Y'),

                ImageColumn::make('img_before_path')
                ->label('รูปภาพอันตราย (ก่อน)')
                ->square(),

                TextColumn::make('hazardLevel.level_name')
                ->label('ระดับความอันตราย')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('hazardType.type_name')
                ->label('ประเภทอันตราย')
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
                ->label('วันที่ปิดจบ CAR')
                ->dateTime('d/m/Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('responsible.dept_name')
                ->label('แผนกผู้รับผิดชอบ'),

                TextColumn::make('users.FullName')
                ->label('ผู้สร้าง'),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->dateTime('d/m/Y H:i')
                //->dateTimeTooltip()
                ->timezone('Asia/Bangkok'),

                TextColumn::make('follow_car')
                ->label('เลขที่ CAR เปิดใหม่')
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
