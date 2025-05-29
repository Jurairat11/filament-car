<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Tables;
use Filament\Pages\Page;
use App\Models\Car_report;
use Livewire\Attributes\Layout;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Request;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Models\Car_responses;


class DepartmentCarAlert extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'All CAR Reported';
    protected static ?string $navigationGroup = 'Car Responses';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.department-car-alert';
    protected function getTableQuery(): Builder
    {
        $query = Car_report::query();

        // Add your existing role logic if needed
        if (Auth::user()?->hasRole('User')) {
            $query->latest();
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('car_no')
            ->label('CAR no.')
            ->searchable(),

            ImageColumn::make('img_before')
            ->label('Picture before'),

            TextColumn::make('car_due_date')
            ->label('Due date')
            ->dateTime('d/m/Y')
            ->timezone('Asia/Bangkok'),

            TextColumn::make('hazardLevel.level_name')
                ->label('Hazard level')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('hazardType.type_name')
                ->label('Stop type')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

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
                    'accepted' => 'accepted',
                    'reported' => 'reported',
                    'in_progress' => 'in progress',
                    'pending_review' => 'pending review',
                    'dismissed' => 'dismissed',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),
            TextColumn::make('users.FullName')
                ->label('Created by')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('responsible.dept_name')
                ->label('Reported to'),

            // DayLeft::make('Remaining Days'),

            TextColumn::make('remaining_days')
                ->label('Remaining')
                ->getStateUsing(function ($record) {
                    // เงื่อนไข: ถ้าตอบกลับแล้ว และสถานะคือ pending_review
                    if (in_array($record->status, ['pending_review','closed','reopened'])) {
                        return 'Replied';
                    }

                    // คำนวณวันตามปกติ
                    $carDate = Carbon::parse($record->car_date);
                    $dueDate = $carDate->addDays($record->car_delay ?? 0);
                    $remaining = round(now()->floatDiffInDays($dueDate, false));
                    $unit = abs($remaining) === 1 ? 'day' : 'days';

                    // Update status_delay if remaining == -1
                    if ($remaining === -1 && $record->status_delay !== 'delay') {
                        $record->status_delay = 'delay';
                        $record->save();
                    }

                    return "{$remaining} {$unit}";
                })
                ->color(function ($record) {
                    if (in_array($record->status, ['pending_review','closed','reopened'])) {
                        return 'info';
                    }
                    return now()->gt(Carbon::parse($record->car_date)->addDays($record->car_delay)) ? 'danger' : 'success';
                }),

            // TextColumn::make('status_delay')
            //     ->label('Reply status')
            //     ->badge()
            //     ->color(fn (string $state): string => match ($state) {
            //         'on_process' => 'warning',
            //         'finished' => 'success',
            //         'delay' => 'danger',
            //         'none' => 'gray'
            //     })
            //     ->formatStateUsing(fn (string $state) => match ($state) {
            //         'on_process' => 'on process',
            //         'finished' => 'finished',
            //         'delay' => 'delay',
            //         'none' => 'none',
            //         default => ucfirst($state),
            //     }),


        ];
    }
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('responsible_id')
            ->label('Department')
            ->relationship('responsible', 'dept_name')
            ->preload()
            ->indicator('Department'),

            SelectFilter::make('hazard_level_id')
                ->label('Hazard level')
                ->relationship('hazardLevel', 'level_name')
                ->preload()
                ->indicator('Hazard level')
                ->default(request('hazard_level_id')),

            SelectFilter::make('hazard_type_id')
                ->label('Stop type')
                ->relationship('hazardType', 'type_name')
                ->preload()
                ->indicator('Stop type')
                ->default(request('hazard_type_id')),

            SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'reported' => 'Reported',
                    'in_progress' => 'In progress',
                    'pending_review' => 'Pending review',
                    'reopened' => 'Reopened',
                    'closed' => 'Closed'
                ])->indicator('status'),

            // SelectFilter::make('status_delay')
            // ->options([
            //     'on_process' => 'On process',
            //     'finished' => 'Finished',
            //     'delay' => 'Delay',
            //     'none' => 'None'
            // ])->indicator('Status delay')->default(request('status_delay'))
        ];

    }
    protected function getTableFiltersLayout(): ?string
    {
        return 'above-content'; // Replace with the correct layout string
    }
    protected function getTableBulkActions(): array
    {
        return [
            DeleteBulkAction::make()
                ->label('Delete Selected')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
            ->label('View')
            ->icon('heroicon-m-eye')
            ->color('gray')
            ->url(fn ($record) => route('filament.admin.resources.car-reports.view', ['record' => $record]))
            ->openUrlInNewTab(),

            Action::make('accept')
            ->label('Acknowledge')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Car_report $record) => $record->status === 'reported' && $record->responsible_dept_id === Auth::user()->dept_id)
            ->action(function (Car_report $record) {
                // อัปเดตสถานะ
                $record->update(['status' => 'in_progress'] );
                $record->update(['status_delay' => 'on_process'] );

                // $id = $this->record->id;
                //     Car_responses::where('car_id', $id)
                //     ->update(['status_reply' => 'on_process']);

                // แจ้งไปยังผู้มี role = safety
                User::role('Safety')->get()
                    ->each(fn ($user) =>
                        Notification::make()
                            ->iconColor('success')
                            ->icon('heroicon-o-document-check')
                            ->title('CAR report accepted')
                            ->body("The P-CAR for CAR no: {$record->car_no} has been accepted.")
                            ->sendToDatabase($user)
                    );
            }),
        ];
    }

    // แสดง badge ตัวเลขการแจ้งเตือน reported ที่ตรงกับแผนกผู้ใช้
    public static function getNavigationBadge(): ?string
    {
        // ตรวจสอบว่าผู้ใช้เป็นแผนกหรือไม่
        if (Auth::user()?->role === 'User') {
            return null;
        }

        // นับเฉพาะที่เป็น reported และแผนกตรงกับผู้ใช้
        return Car_report::where('status', 'reported')
            ->where('responsible_dept_id', Auth::user()->dept_id)
            ->count();
    }

}
