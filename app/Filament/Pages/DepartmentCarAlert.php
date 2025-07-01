<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Tables;
use Filament\Pages\Page;
use App\Models\Car_report;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\DeleteBulkAction;


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
        $query = Car_report::query()->latest();

        // Add your existing role logic if needed
        if (Auth::user()?->hasRole('User')) {
            $query;
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('car_no')
            ->label('CAR no.')
            ->searchable(),

            TextColumn::make('car_due_date')
            ->label('Due date'),

            ImageColumn::make('img_before_path')
            ->label('Picture before')
            ->square(),

            TextColumn::make('hazardLevel.level_name')
                ->label('Hazard level')
                ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('hazardType.type_name')
                ->label('Hazard type')
                ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('car_due_date')
            ->label('Due date')
            ->dateTime('d/m/Y')
            ->timezone('Asia/Bangkok'),

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

            TextColumn::make('users.FullName')
                ->label('Created by')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('responsible.dept_name')
                ->label('Reported to')
                ->searchable(),
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
                ->label('Hazard type')
                ->relationship('hazardType', 'type_name')
                ->preload()
                ->indicator('Hazard type')
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
        ];

    }
    protected function getTableFiltersLayout(): ?string
    {
        return 'above-content'; // Replace with the correct layout string
    }
    protected function getTableBulkActions(): array
    {
        return [
            // DeleteBulkAction::make()
            //     ->label('Delete Selected')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('acknowledge')
            ->label('Acknowledge')
            ->tooltip('Acknowledge car report')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Car_report $record) => $record->status === 'reported' && $record->responsible_dept_id === Auth::user()->dept_id)
            ->action(function (Car_report $record) {
                // อัปเดตสถานะ
                $record->update(['status' => 'in_progress'] );

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

            Action::make('view')
            ->label('View')
            ->icon('heroicon-m-eye')
            ->color('gray')
            ->url(fn ($record) => route('filament.admin.resources.car-reports.view', ['record' => $record]))
            ->visible(in_array(Auth::user()->role,['Admin','Safety'])),

            Action::make('view')
            ->label('View')
            ->icon('heroicon-m-eye')
            ->color('gray')
            ->url(fn ($record) => route('infolists.components.view-car-details', ['record' => $record]))
            ->visible(Auth::user()->role === 'User')
        ];
    }

    // แสดง badge ตัวเลขการแจ้งเตือน reported ที่ตรงกับแผนกผู้ใช้
    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->hasRole('User')) {

             // นับเฉพาะที่เป็น reported และแผนกตรงกับผู้ใช้
        return Car_report::where('status', 'reported')
                ->where('responsible_dept_id', Auth::user()->dept_id)
                ->count();
        }
        return null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New CAR report';
    }

}
