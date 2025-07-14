<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Car_report;
use App\Models\Car_responses;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class followCarTable extends BaseWidget
{
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Following CAR Overview';
    public function table(Table $table): Table {

        return $table
            ->emptyStateHeading('No data available')
            ->emptyStateDescription('No data available for this table.')
            ->emptyStateIcon('heroicon-o-bookmark')
            ->query(
                Car_report::query()
                    ->where('responsible_dept_id',Auth::user()->dept_id)
                    ->leftJoin('car_responses', 'car_reports.id', '=','car_responses.car_id')
                    ->select('car_reports.*', 'car_responses.temp_desc', 'car_responses.temp_status', 'car_responses.perm_desc', 'car_responses.perm_status','car_responses.status_reply')
            )
            ->defaultSort('created_at', 'desc') //sort order by created_at
            ->columns([
                TextColumn::make('car_no')
                    ->label('CAR no.')
                    ->searchable(),

                TextColumn::make('hazardLevel.level_name')
                    ->label('Hazard Level'),

                TextColumn::make('place.place_name')
                    ->label('Place'),

                TextColumn::make('car_desc')
                    ->label('Hazard Description')
                    ->limit(50),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'reported' => 'info',
                    'on_process' => 'warning',
                    'pending_review' => 'success',
                    'reopened' => 'warning',
                    'closed' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'draft' => 'draft',
                    'reported' => 'reported',
                    'on_process' => 'on process',
                    'pending_review' => 'pending review',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),

                TextColumn::make('temp_desc')
                    ->label('Temporary C/M')
                    ->limit(20),

                TextColumn::make('temp_status')
                    ->label('Temp. Status')
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
                    ->label('Permanent C/M')
                    ->limit(20),

                TextColumn::make('perm_status')
                    ->label('Perm. Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                    'on process' => 'warning',
                    'finished' => 'success',
                    default => 'gray'

                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on process' => 'on process',
                        'finished' => 'finished',

                        default => ucfirst($state),
                    }),

                TextColumn::make('status_reply')
                    ->label('Reply status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'on process' => 'warning',
                        'finished' => 'success',
                        'delay' => 'danger',
                        // default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'on process' => 'on process',
                        'finished' => 'finished',
                        'delay' => 'delay',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Filter::make('car_reports.created_at')
                ->form([
                    DatePicker::make('created_from')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                    DatePicker::make('created_until')->native(false)->displayFormat('d/m/Y')->placeholder('dd/mm/yyyy'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('car_reports.created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('car_reports.created_at', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from']) {
                        $indicators[] = 'from: ' . Carbon::parse($data['created_from'])->format('d/m/Y');
                        }

                        if ($data['created_until']) {
                        $indicators[] = 'until: ' . Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                }),
                        SelectFilter::make('status_reply')
                        ->label('Reply status')
                        ->options([
                            'on_process' => 'On process',
                            'finished'=> 'Finished',
                            'delay' => 'Delay',
                        ])->indicator('Reply status'),
                ])
            ->actions([
                ViewAction::make()
                ->label('View')
                ->url(fn (Car_report $record): string =>
                    route('filament.admin.resources.car-responses.index', [
                        'status_reply' => $record->status_reply
                    ])
                )
                ->visible(fn(Car_responses $car_responses) =>  $car_responses->temp_desc !== null)
                ->icon('heroicon-m-eye')
                ->color('primary')
                ->openUrlInNewTab()
                ->action(function (Car_report $record) {
                    $this->redirect(
                        route('filament.admin.resources.car-responses.index', [
                            'status_reply' => $record->status_reply
                        ])
                    );
                }),
            ]);
        }
        public static function canView(): bool
        {
            return Auth::user()->hasRole('User');
        }
    }


