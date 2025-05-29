<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Forms\Form;
use App\Models\Car_report;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Models\Car_responses;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\CarResponsesResource;

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
                $record->status === 'draft' &&
                $record->carReport?->responsible_dept_id === Auth::user()->dept_id
            )
            ->action(function () {
                // ปิดใบปัจจุบัน
                $this->record->update(['status' => 'pending_review']);

                if ($this->record->car_id) {
                    Car_report::where('id', $this->record->car_id)
                        ->update([
                            'status' => 'pending_review',
                            'status_delay' => 'finished'
                        ]);
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

            $data = [
                'car_id' => $this->record->carReport->car_no ?? '-',
                'cause' => $this->record->cause ?? '-',
                'created_by' => $this->record->createdResponse->emp_id?? '-',
            ];

            $txtTitle = "ตอบกลับใบ CAR";

            // create connector instance
            $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
            // // create card
            // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

            // create a custom card
            $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
            // add information
            $card->setColor('01BC36')
                ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_id'], 'สาเหตุ' => $data['cause']])
                ->addAction('Visit Issue', route('filament.admin.resources.car-responses.view', $this->record));
            // send card via connector
            $connector->send($card);
            }),

        ];
    }

    public function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make('CAR Response')
                ->description(fn ($livewire) =>
                    'Status: ' . ucfirst(str_replace('_', ' ', $livewire->form->getRawState()['status'] ?? '')
                ))
                //ucfirst(str_replace('_', ' ', $livewire->form->getRawState()['status'] ?? '')
                ->schema([

                    View::make('components.car-responses-view-image')
                        ->label('After Image')
                        ->viewData([
                            'path' => $this->getRecord()->img_after,
                        ])->columnSpan(1),

                    Placeholder::make('cause')
                        ->label('Cause')
                        ->columnSpan(2)
                        ->content(fn ($record) => $record->cause ),

                    Placeholder::make('created_by')
                    ->label('Created by')
                    ->content(fn($record)=>optional($record->createdResponse)->FullName),

                ])->columns(4),

                    Section::make('Temporary actions')
                    ->description(fn ($livewire) =>
                    'Status: ' . ucfirst(str_replace('_', ' ', $livewire->form->getRawState()['temp_status'] ?? '')
                ))
                    ->schema([

                        Placeholder::make('temp_desc')
                        ->label('Temporary C/M')
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

                    ])->columns(4),

                    Section::make('Permanent actions')
                    ->description(fn ($livewire) =>
                        'Status: ' . ucfirst(str_replace('_', ' ', $livewire->form->getRawState()['perm_status'] ?? '')
                    ))
                    ->schema([

                            Placeholder::make('perm_desc')
                            ->label('Permanent C/M')
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

                    ])->columns(4),

                    Section::make()
                    ->schema([
                        Placeholder::make('preventive')
                            ->label('Preventive actions')
                            ->columnSpanFull()
                            ->content(fn($record)=>$record->preventive),
                    ])

        ]);
}
}
