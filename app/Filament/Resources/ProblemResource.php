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
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProblemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProblemResource\RelationManagers;

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

                    Select::make('user_id')
                        ->label('Reporter')
                        ->searchable()
                        ->preload()
                        ->relationship('user','emp_id')
                        ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->emp_id} ({$record->emp_name} {$record->last_name})")
                        ->required(),

                    Select::make('dept_id')
                        ->label('Department')
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->options(fn () => Department::all()->pluck('dept_name', 'dept_id'))
                        ->default(fn () => Auth::user()?->dept_id),

                    Textarea::make('prob_desc')
                        ->label('Description')
                        ->autosize()
                        ->required(),

                    DatePicker::make('prob_date')
                        ->label('Report date')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->required(),

                    FileUpload::make('prob_img')
                        ->label('Problem picture')
                        ->image()
                        ->required()
                        ->directory('form-attachments')
                        ->visibility('public'),

                    Select::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'accepted' => 'Accepted',
                        'dismissed' => 'Dismissed',
                        'closed' => 'Closed'
                    ])
                    ->default('new')

                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                        'closed' => 'gray'
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'new',
                        'accepted' => 'accepted',
                        'dismissed' => 'dismissed',
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
                //
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) =>
                        Auth::user()?->hasRole('Safety') && $record->status === 'new'
                    )

                    // ->visible(fn ($record) =>
                    //     Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status === 'new'
                    // )

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
                        Auth::user()?->hasRole('Safety') && $record->status === 'new'
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

    // public static function getNavigationBadge(): ?string
    // {
    //     $count = Problem::where('status', 'new')->count();

    //     return $count > 0 ? (string) $count : null;
    // }
    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
        ->when(! $user->hasRole(['Admin','Safety']), function ($query) use ($user) {
            $query->where('dept_id', $user->dept_id);
        });
    }


}
