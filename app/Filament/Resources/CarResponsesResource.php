<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Car_report;
use Filament\Tables\Table;
use App\Models\Car_responses;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CarResponsesResource\Pages;
use App\Filament\Resources\CarResponsesResource\RelationManagers;

class CarResponsesResource extends Resource
{
    protected static ?string $model = Car_responses::class;
    protected static ?string $navigationLabel = 'ตอบกลับ CAR';
    protected static ?string $pluralModelLabel = 'ตอบกลับ CAR';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'CAR Responses';
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
                        ->label('เลขที่ CAR')
                        ->placeholder('เลือก เลขที่ CAR ที่ต้องการตอบกลับ')
                        ->options(function (){
                            if(Auth::user()?->hasAnyRole(['Admin'])) {
                                return Car_report::all()
                                ->mapWithKeys(fn ($car) => [$car->id => "{$car->car_no} ({$car->status})"]);
                            }
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

                                    //dd($car->car_due_date); "2025-02-07"
                                }
                        }),

                        Hidden::make('status')
                        ->default('draft')
                        ->dehydrated(true),

                        Textarea::make('cause')
                        ->label('สาเหตุ')
                        ->autosize()
                        ->required(),

                        FileUpload::make('img_after_path')
                            ->label('รูปภาพอันตราย (หลัง)')
                            ->helperText('ขนาดสูงสุดไฟล์รูปภาพ 5MB')
                            ->image()
                            ->downloadable()
                            //->acceptedFileTypes(['jpg'])
                            // ->required()
                            ->maxSize(5120) // 5MB
                            ->directory('form-attachments')
                            ->visibility('public'),

                    ]),

                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('มาตรการแก้ไขชั่วคราว')
                        ->schema([
                            Textarea::make('temp_desc')
                            ->label('มาตรการแก้ไขชั่วคราว')
                            ->autosize(),

                            DatePicker::make('temp_due_date')
                            ->label('วันที่กำหนดเสร็จ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(),

                            TextInput::make('temp_responsible')
                            ->label('ผู้รับผิดชอบ')

                        ]),
                    Tabs\Tab::make('มาตรการแก้ไขถาวร')
                        ->schema([
                            Textarea::make('perm_desc')
                            ->label('มาตรการแก้ไขถาวร')
                            ->autosize(),

                            DatePicker::make('perm_due_date')
                            ->label('วันที่กำหนดเสร็จ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            // ->format('d/m/Y')
                            ->disabled()
                            ->dehydrated(true),

                            // เพิ่มวันที่คาดการณ์ว่าจะกำหนดเสร็จ
                            DatePicker::make('actual_date')
                            ->label('วันที่คาดการณ์จะเสร็จสิ้น')
                            ->native(false)
                            ->displayFormat('d/m/y')
                            ->placeholder('dd-mm-yyyy')
                            ->closeOnDateSelection()
                            ->helperText(new HtmlString('<strong style="color:red;">*ใส่ในกรณีมาตรการแก้ไขถาวรใช้เวลาเลยวันที่กำหนดเสร็จ</strong>'))
                            ->disabled(function (?Model $record) {
                                // ถ้าเป็น admin ไม่ disabled
                                if (Auth::user()?->hasRole('Admin')) {
                                    return false;
                                }

                                // ถ้า actual_date ถูกกรอกแล้ว ให้ disabled
                                return filled($record?->actual_date);
                            }),

                            TextInput::make('perm_responsible')
                            ->label('ผู้รับผิดชอบ'),

                            Textarea::make('preventive')
                            ->label('กำหนดมาตรการป้องกันการเกิดปัญหาซ้ำ')
                            ->autosize()
                            ->nullable(),

                            Hidden::make('days_perm_value')
                            ->dehydrated(true)
                            ->default(0)

                        ]),
                    ]),
                ]),
            ])->columns(1)->columnSpan(2),

                Hidden::make('created_by')
                ->label('Created by')
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
            ->emptyStateHeading('No data available')
            ->columns([
                TextColumn::make('carReport.car_no')
                ->label('CAR no.')
                ->searchable(),

                ImageColumn::make('img_after_path')
                ->label('รูปภาพอันตราย (หลัง)')
                ->square(),

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
                ->label('ผู้สร้าง')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->timezone('Asia/Bangkok')
                ->dateTime('d/m/Y H:i')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('temp_desc')
                ->label('มาตรการแก้ไขชั่วคราว')
                ->limit(50)
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('temp_due_date')
                ->label('วันที่ครบกำหนดแก้ไขชั่วคราว')
                ->timezone('Asia/Bangkok')
                ->dateTime('d/m/Y'),

                TextColumn::make('temp_status')
                    ->label('สถานะการตอบกลับ (ชั่วคราว)')
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
                    ->label('มาตรการแก้ไขถาวร')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('perm_due_date')
                    ->label('วันที่ครบกำหนดแก้ไขถาวร')
                    ->timezone('Asia/Bangkok')
                    ->dateTime('d/m/Y'),

                TextColumn::make('days_perm')
                    ->label('จำนวนวันคงเหลือ'),

                TextColumn::make('perm_status')
                    ->label('สถานะการตอบกลับ (ถาวร)')
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
                    ->label('สถานะการตอบกลับ')
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
            //edit click
            ->recordAction('edit')
            ->recordUrl(null)

            ->actions([
                Action::make('checked')
                ->label('Check')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading("Checked the car report.")
                ->modalDescription('The car responses have been checked.')
                ->modalSubmitActionLabel('OK')
                ->visible(fn ($record) =>
                        Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status_reply !== 'finished'
                )
                ->action(function($record, array $data) {

                        $record->update([
                            'perm_status' => 'finished',
                            'status_reply' => 'finished'
                        ]);
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                    ->modalHeading(fn ($record) => 'Edit CAR responses')
                    ->visible(fn ($record) =>
                    Auth::user()?->hasRole('User')
                        ? $record->status_reply !== 'finished'
                        : true
                ),
                    Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) =>
                    Auth::user()?->hasRole('User')
                        ? $record->status_reply !== 'finished'
                        : true
                ),

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
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()

                // ]),
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

    public static function getNavigationBadge(): ?string
    {

        if (Auth::user()->hasAnyRole(['Safety','Admin'])) {
            return (string) static::$model::where('perm_status', 'on_process')->count();
        }
        return null;

    }
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Check CAR responses';
    }

}
