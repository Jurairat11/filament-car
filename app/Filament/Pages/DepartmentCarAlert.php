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
    protected static ?string $title = 'รับเรื่อง CAR';
    protected static ?string $navigationGroup = 'CAR Responses';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.department-car-alert';
    protected function getTableQuery(): Builder
    {
        $query = Car_report::query()->latest()
        ->whereNot('status','draft');

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

            TextColumn::make('car_date')
            ->label('วันที่สร้าง CAR'),

            ImageColumn::make('img_before_path')
            ->label('รูปภาพอันตราย (ก่อน)')
            ->square(),

            TextColumn::make('hazardLevel.level_name')
                ->label('ระดับความอันตราย')
                ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('hazardType.type_name')
                ->label('ประเภทของอันตราย')
                ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('car_due_date')
            ->label('วันที่ครบกำหนดแก้ไข CAR')
            ->dateTime('d/m/Y')
            ->timezone('Asia/Bangkok'),

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

            TextColumn::make('users.FullName')
                ->label('ผู้สร้าง')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('responsible.dept_name')
                ->label('แผนกผู้รับผิดชอบ')
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
                    'on_process' => 'On process',
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

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No data available';
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
            ->tooltip('Acknowledge CAR report')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Acknowledge CAR')
            ->modalDescription('Your department has acknowledged the car report.')
            ->modalSubmitActionLabel('OK')
            ->visible(fn (Car_report $record) => $record->status === 'reported' && $record->responsible_dept_id === Auth::user()->dept_id)
            ->action(function (Car_report $record) {
                // อัปเดตสถานะ
                $record->update(['status' => 'on_process'] );

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
            ->visible(Auth::user()?->hasAnyRole(['Admin','Safety'])),

            Action::make('view')
            ->label('View')
            ->icon('heroicon-m-eye')
            ->color('gray')
            //->url(fn ($record) => route('', ['record' => $record]))
            ->url(fn ($record) => route('filament.admin.pages.view-car-report-custom.{record}', ['record' => $record->id]))
            ->visible(Auth::user()?->hasRole('User'))
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasAnyRole(['User', 'Admin']);
    }

}
