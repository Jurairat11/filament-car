<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\User;
use Filament\Tables;
use App\Models\Problem;
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
use Filament\Tables\Enums\FiltersLayout;
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
                    ->options(fn() => Problem::where('status', 'accepted')->pluck('prob_id', 'id'))
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
                    ->label('Create Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->required(),

                    DatePicker::make('car_due_date')
                    ->label('Due Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->required(),

                    Textarea::make('car_desc')
                    ->label('Description')
                    ->autosize()
                    ->required(),

                    Select::make('hazard_level_id')
                    ->label('Hazard level')
                    ->relationship('hazardLevel','level_name')
                    ->required(),

                    Select::make('hazard_type_id')
                    ->label('Hazard type')
                    ->relationship('hazardType','type_name')
                    ->required(),

                    FileUpload::make('img_before')
                    ->label('Picture before')
                    ->image()
                    ->required()
                    ->directory('form-attachments')
                    ->visibility('public'),

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
                    ->default('accepted'),

                    Select::make('created_by')
                    ->label('Created by')
                    ->relationship('users','emp_id')
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                    ->searchable()
                    ->preload()
                    ->required(),

                    Select::make('responsible_dept_id')
                    ->label('Reported to')
                    ->searchable()
                    ->preload()
                    ->relationship('responsible','dept_name'),

                    Hidden::make('parent_car_id')
                    ->dehydrated(true)
                    ->default(request()->get('parent_car_id')),

                    Hidden::make('followed_car_id')
                    ->dehydrated(true)


                ])->columns(2)
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
                ->dateTime('d/m/Y'),
                TextColumn::make('car_due_date')
                ->label('Due date')
                ->sortable()
                ->dateTime('d/m/Y'),
                ImageColumn::make('img_before')
                ->label('Picture before'),
                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'primary',
                    'accepted' => 'success',
                    'reported' => 'info',
                    'in_progress' => 'warning',
                    'pending_review' => 'success',
                    'dismissed' => 'danger',
                    'reopened' => 'warning',
                    'closed' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'new' => 'new',
                    'reported' => 'reported',
                    'accepted' => 'accepted',
                    'in_progress' => 'in progress',
                    'pending_review' => 'pending review',
                    'dismissed' => 'dismissed',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),

                TextColumn::make('users.FullName')
                ->label('Created by'),

                TextColumn::make('created_at')
                ->label('Created at')
                ->sortable()
                ->dateTime('d/m/Y H:i')
                //->dateTimeTooltip()
                ->timezone('Asia/Bangkok'),

            ])
            ->filters([
                SelectFilter::make('responsible_id')
                ->label('Department')
                ->relationship('responsible', 'dept_name')
                ->searchable()
                ->preload()
                ->indicator('Department'),

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
