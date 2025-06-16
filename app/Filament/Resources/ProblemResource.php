<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use App\Models\Problem;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ProblemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProblemResource\RelationManagers;
use App\Filament\Resources\ProblemResource\Pages\EditProblem;
use App\Filament\Resources\ProblemResource\Pages\ViewProblem;
use App\Filament\Resources\ProblemResource\Pages\ListProblems;
use App\Filament\Resources\ProblemResource\Pages\CreateProblem;

class ProblemResource extends Resource
{
    protected static ?string $model = Problem::class;
    protected static ?string $navigationGroup = 'Car Responses';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Problem Details')
                ->description(fn ($livewire) =>
                    'Problem ID: ' . ($livewire->form->getRawState()['prob_id'] ?? 'new')
                )

                ->schema([
                    Hidden::make('prob_id')
                        ->label('Problem ID')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Split::make([
                    Section::make()
                    ->schema([
                        Select::make('user_id')
                        ->label('Reporter')
                        ->searchable()
                        ->preload()
                        ->relationship('user','emp_id',function ($query){
                            $query->where('dept_id',Auth::user()->dept_id);
                        })
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                        //->options(fn () => User::where('dept_id',Auth::user()?->dept_id)->pluck('emp_id', 'id'))
                        //->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                        ->required(),

                    Select::make('dept_id')
                        ->label('Department')
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->options(fn () => Department::all()->pluck('dept_name', 'dept_id'))
                        ->default(fn () => Auth::user()?->dept_id),

                    DatePicker::make('prob_date')
                        ->label('Report date')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->required(),
                    ])->columns(1),

                    Section::make()
                    ->schema([
                        TextInput::make('title')
                        ->label('Title')
                        ->required(),

                        TextInput::make('place')
                        ->label('Place')
                        ->required(),

                        FileUpload::make('prob_img')
                        ->label('Problem picture')
                        ->image()
                        ->downloadable()
                        ->required()
                        ->directory('form-attachments')
                        ->visibility('public')
                        ->columnSpanFull(),

                        Textarea::make('prob_desc')
                            ->label('Description')
                            ->autosize()
                            ->required()
                            ->columnSpanFull(),

                            ])->columns(2),
                        ])->from('md'),
                    ]),

                    Hidden::make('status')
                    ->default('new')
                    ->dehydrated(true)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at','desc')
            ->columns([
                TextColumn::make('prob_id')
                    ->label('ID'),
                TextColumn::make('user.FullName')
                    ->label('Reporter'),
                TextColumn::make('department.dept_name')
                    ->label('Department'),
                ImageColumn::make('prob_img')
                    ->label('Image'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'accepted' => 'success',
                        'dismissed' => 'danger',
                        'reported' => 'warning',
                        'closed' => 'gray'
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'new',
                        'accepted' => 'accepted',
                        'dismissed' => 'dismissed',
                        'reported' => 'reported',
                        'closed' => 'closed',
                        default => ucfirst($state),
                    }),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime('d/m/Y H:i')
                    ->timezone('Asia/Bangkok'),
            ])
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'new' => 'New',
                    'accepted' => 'Accepted',
                    'dismissed' => 'Dismissed',
                    'reported' => 'Reported',
                    'closed' => 'Closed'
                ]) ->indicator('status'),

                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')->native(false)->displayFormat('d/m/y')->placeholder('dd/mm/yyyy'),
                    DatePicker::make('created_until')->native(false)->displayFormat('d/m/y')->placeholder('dd/mm/yyyy'),
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
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) =>
                        Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status === 'new'
                    )

                    ->action(function($record, array $data) {
                        $record->update([
                            'status' => 'accepted',
                        ]);
                        // แจ้งเตือนกลับไปยังผู้แจ้งปัญหา
                        $employee = User::where('id', $record->user_id)->first();

                        if ($employee) {
                            Notification::make()
                                ->icon('heroicon-o-check-circle')
                                ->iconColor('success')
                                ->title('Problem Accepted')
                                ->body("Problem ID: {$record->prob_id} was accepted.")
                                ->sendToDatabase($employee);
                        }
                        return redirect(route('filament.admin.resources.car-reports.create', [
                            'prob_id' => $record->prob_id,
                        ]));

                        }),

                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                Textarea::make('dismiss_reason')
                        ->label('Reason for dismissal')
                        ->required()
                        ->autosize()
                        ->maxLength(500),
                ])
                ->visible(fn ($record) =>
                        Auth::user()?->hasAnyRole(['Safety','Admin']) && $record->status === 'new'
                    )
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => 'dismissed',
                        'dismiss_reason' => $data['dismiss_reason'],
                    ]);

                    // แจ้งเตือนกลับไปยังผู้แจ้งปัญหา
                    $employee = User::where('id', $record->user_id)->first();

                    if ($employee) {
                        Notification::make()
                            ->icon('heroicon-o-x-circle')
                            ->iconColor('danger')
                            ->title('Problem Dismissed')
                            ->body("Problem ID: {$record->prob_id} was dismissed.\nReason: {$data['dismiss_reason']}")
                            ->sendToDatabase($employee);
                    }

                })

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
            'index' => Pages\ListProblems::route('/'),
            'create' => Pages\CreateProblem::route('/create'),
            'view' => Pages\ViewProblem::route('/{record}'),
            'edit' => Pages\EditProblem::route('/{record}/edit'),
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
        ->when(! $user->hasRole(['Admin','Safety']), function ($query) use ($user) {
            $query->where('dept_id', $user->dept_id);
        });
    }

    public static function getNavigationBadge(): ?string
    {

        if (Auth::user()->hasAnyRole(['Safety','Admin'])) {
            return (string) static::$model::where('status', 'new')->count();
        }
        return null;

    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New problem report';
    }


}
