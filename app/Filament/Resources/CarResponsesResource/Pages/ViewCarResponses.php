<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use App\Filament\Resources\CarResponsesResource;
use App\Models\Car_report;
use App\Models\Car_responses;
use Filament\Actions\Action;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\View;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewCarResponses extends ViewRecord
{
    protected static string $resource = CarResponsesResource::class;
    protected static ?string $title = 'View CAR Responses';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Response')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Car_responses $record) =>
                Auth::check() &&
                Auth::user()->hasRole('User') &&
                $record->status === 'in_progress' &&
                $record->carReport?->responsible_dept_id === Auth::user()->dept_id
            )
            ->action(function () {
                // ปิดใบปัจจุบัน
                $this->record->update(['status' => 'pending_review']);

                if ($this->record->car_id) {
                    Car_report::where('id', $this->record->car_id)
                        ->update(['status' => 'pending_review']);
        }
            User::role('Safety')->get()
            ->each(fn ($user) =>
                Notification::make()
                    ->iconColor('success')
                    ->icon('heroicon-o-document-check')
                    ->title('Department response submitted')
                    ->body("CAR report CAR no: {$this->record->carReport->car_no} has been responded to.")
                    ->sendToDatabase($user)
            );

            }),

        ];
    }

    public function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('CAR Response Information')
                ->schema([

                    View::make('components.car-responses-view-image')
                        ->label('After Image')
                        ->viewData([
                            'path' => $this->getRecord()->img_after,
                        ])->columnSpanFull(),

                    Placeholder::make('cause')
                    ->label('Cause')
                    ->columnSpan(2)
                    ->content(fn ($record) => $record->cause ),

                    Placeholder::make('status')
                    ->label('Status')
                    ->content(fn ($record) =>
                    ucfirst(str_replace('_', ' ', $record->status))),

                    Placeholder::make('created_by')
                    ->label('Created by')
                    ->content(fn($record)=>optional($record->createdResponse)->FullName),

                    Placeholder::make('temp_desc')
                    ->label('Temporary Action')
                    ->columnSpan(2)
                    ->content(fn($record)=>$record->temp_desc ? $record->temp_desc : '-'),

                    Placeholder::make('temp_due_date')
                    ->label('Due date')
                    ->content(fn ($record) => $record->temp_due_date
                                ? Carbon::parse($record->temp_due_date)->format('d/m/Y')
                                : '-'),

                    Placeholder::make('temp_responsible')
                    ->label('Responsible')
                    ->content(fn($record)=>optional($record->tempResponsible)->FullName ?
                    ($record->tempResponsible)->FullName : '-'),

                    Placeholder::make('perm_desc')
                    ->label('Permanent Action')
                    ->columnSpan(2)
                    ->content(fn($record)=>$record->perm_desc ? $record->perm_desc : '-'),


                    Placeholder::make('perm_due_date')
                    ->label('Due date')
                    ->content(fn ($record) => $record->perm_due_date
                                ? Carbon::parse($record->perm_due_date)->format('d/m/Y')
                                : '-'),

                    Placeholder::make('perm_responsible')
                    ->label('Responsible')
                    ->content(fn($record)=>optional($record->permResponsible)->FullName ?
                    ($record->permResponsible)->FullName : '-'),

                    Placeholder::make('preventive')
                    ->label('Preventive action')
                    ->columnSpanFull()
                    ->content(fn($record)=>$record->preventive),

                ])->columns(4)
        ]);
}
}
