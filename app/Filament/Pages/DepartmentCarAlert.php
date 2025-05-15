<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Tables;
use Filament\Pages\Page;
use App\Models\Car_report;
use App\Tables\Columns\DayLeft;
use Livewire\Attributes\Layout;
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
    {   //หน่วยงานเห็นทั้งหมด
        return Car_report::query()
            ->where(fn ($query) => Auth::user()?->hasRole('User'))
            //->whereNotIn('status', ['pending_review', 'closed'])
            ->latest();
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

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'primary',
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
                    'in_progress' => 'in progress',
                    'pending_review' => 'pending review',
                    'dismissed' => 'dismissed',
                    'reopened' => 'reopened',
                    'closed' => 'closed',
                    default => ucfirst($state),
                }),
            TextColumn::make('users.FullName')
                ->label('Created by'),

            TextColumn::make('responsible.dept_name')
                ->label('Reported to'),

            DayLeft::make('Remaining Days')

        ];
    }
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('responsible_id')
            ->relationship('responsible', 'dept_name')
            ->searchable()
            ->preload()
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
                $record->update(['status' => 'in_progress']);

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
        if (Auth::user()?->role !== 'department') {
            return null;
        }

        // นับเฉพาะที่เป็น reported และแผนกตรงกับผู้ใช้
        return Car_report::where('status', 'reported')
            ->where('responsible_dept_id', Auth::user()->dept_id)
            ->count();
    }

}
