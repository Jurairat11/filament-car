<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use App\Filament\Resources\CarReportResource;
use Filament\Actions\Action;
use App\Models\Problem;
use App\Models\User;
use Filament\Notifications\Notification;
use App\Models\Car_responses;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\View;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCarReport extends ViewRecord
{
    protected static string $resource = CarReportResource::class;
    protected static ?string $title = 'View CAR Report';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('Report')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn ($record) =>
                        Auth::user()?->hasRole('Safety') && $record->status === 'accepted'
                    )
            ->action(function($record, array $data) {
                $record->update([
                    'status' => 'reported',
                    //'responsible_dept_id' => $data['responsible_dept_id'],
                    // ไม่ต้องอัปเดต dept_id
                ]);
                Problem::where('id', $record->problem_id)->update(['status' => 'reported']);

                //แจ้งหน่วยงานที่รับผิดชอบ
                $departmentUsers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'User');
                })
                ->where('dept_id', $this->record->responsible_dept_id)
                ->get();

                foreach ($departmentUsers as $user) {
                Notification::make()
                    ->iconColor('warning')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->title('P-CAR reported')
                    ->body("The P-CAR for CAR no: {$this->record->car_no} has been reported.")
                    ->sendToDatabase($user);
            }
        }),

            Action::make('Reopen')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn ($record) =>
            User::role('Safety') && $record->status === 'pending_review')
            ->action(function () {
                $this->record->update(['status' => 'reopened']);
                // ปิดใบ CAR ก่อนหน้า
                if ($this->record->parent_car_id) {
                    $this->record->parent()->update(['status' => 'reopened']);
                }

                // ปิด responses ที่เกี่ยวข้อง
                $id = $this->record->id;
                Car_responses::where('car_id', $id)->update(['status' => 'reopened']);

                //แจ้งหน่วยงานที่รับผิดชอบ
                $deptUsers = User::role('User')
                ->where('dept_id', $this->record->responsible_dept_id)
                ->get();

                foreach ($deptUsers as $user) {
                    Notification::make()
                        ->iconColor('warning')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->title('P-CAR reopened')
                        ->body("The resolution for CAR no: {$this->record->car_no} was not accepted. A new P-CAR will be created.")
                        ->sendToDatabase($user);
                }

                 // ส่งค่าผ่าน query string
                return redirect()->route('filament.admin.resources.car-reports.create', [
                    'problem_id'           => $this->record->problem_id,
                    'dept_id'              => $this->record->dept_id,
                    'sec_id'               => $this->record->sec_id,
                    'car_date'             => $this->record->car_date,
                    'car_due_date'         => $this->record->car_due_date,
                    'car_desc'             => $this->record->car_desc,
                    'hazard_level_id'      => $this->record->hazard_level_id,
                    'hazard_type_id'       => $this->record->hazard_type_id,
                    'img_before'           => $this->record->img_before,
                    'responsible_dept_id'  => $this->record->responsible_dept_id,
                    'parent_car_id'        => $this->record->id, //ชี้กลับไปยัง CAR แม่
                     //ชึ้ไปยัง CAR ลูกที่ตามมา
                ]);
            }),

            Action::make('Close')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn ($record) =>
                User::role('Safety') && $record->status === 'pending_review')
                ->action(function () {
                    // ปิดใบปัจจุบัน
                    $this->record->update(['status' => 'closed']);

                    // ปิดใบ CAR ก่อนหน้า
                    if ($this->record->parent_car_id) {
                        $this->record->parent()->update(['status' => 'closed']);
                    }

                    // ปิดปัญหาต้นทาง
                    if ($this->record->problem_id) {
                        Problem::where('id', $this->record->problem_id)
                            ->update(['status' => 'closed']);
                    }

                    // ปิด responses ที่เกี่ยวข้อง
                    $id = $this->record->id;
                    Car_responses::where('car_id', $id)->update(['status' => 'closed']);
                    //dd($this->record->id);

                    $parent_car_id = $this->record->parent_car_id;
                    Car_responses::where('car_id',$parent_car_id)->update(['status'=> 'closed']);
                    //dd($this->record->parent_car_id);

                    //แจ้งพนักงานผู้แจ้งปัญหา
                    $problem = Problem::where('id', $this->record->problem_id)->first();
                    //dd($this->record->problem_id);
                    //dd($problem); 7

                    if ($problem) {
                        $employee = User::where('id', $problem->user_id)->first();
                        //dd($problem->user_id); 4
                        if ($employee) {
                            Notification::make()
                                ->iconColor('success')
                                ->icon('heroicon-o-check-circle')
                                ->title('Your issue has been solved')
                                ->body("Your Problem ID: {$problem->prob_id} has been resolved and closed.")
                                ->sendToDatabase($employee);
                        }
                    }

                    //แจ้งหน่วยงานที่รับผิดชอบ
                    $departmentUsers = User::role('User')
                    ->where('dept_id', $this->record->responsible_dept_id)
                    ->get();

                    foreach ($departmentUsers as $user) {
                    Notification::make()
                        ->iconColor('success')
                        ->icon('heroicon-o-check-circle')
                        ->title('P-CAR completed')
                        ->body("The P-CAR for CAR no: {$this->record->car_no} has been completed successfully.")
                        ->sendToDatabase($user);
                }
            }),
        ];
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([
                Section::make('Problem Details')
                ->visible(fn($record) => $record->problem !== null)
                ->schema([
                    Placeholder::make('prob_id')
                        ->label('Problem ID')
                        ->content(fn ($record) => $record->problem?->prob_id),

                    Placeholder::make('user_id')
                        ->label('Employee ID')
                        ->content(fn ($record) => optional($record->problem?->user)->FullName),

                    Placeholder::make('dept_id')
                        ->label('Department')
                        ->content(fn ($record) => optional($record->problem?->department)->dept_name),

                    Placeholder::make('prob_desc')
                        ->label('Problem Description')
                        ->content(fn ($record) => $record->problem?->prob_desc)
                        ->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(3),

                Section::make('CAR Information')
                ->description(fn ($livewire) =>
                    'CAR No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
                )
                ->schema([
                    Placeholder::make('problem_id')
                        ->label('Problem ID')
                        ->content(fn($record)=>optional($record->problem)->prob_id),

                    Placeholder::make('dept_id')
                        ->label('Department')
                        ->content(fn ($record) => optional ($record->department)->dept_name ),

                    Placeholder::make('sec_id')
                        ->label('Section')
                        ->content(fn($record)=>optional($record->section)->sec_name),

                    Placeholder::make('car_date')
                        ->label('Created date')
                        ->content(fn($record)=>Carbon::parse($record->car_date)->format('d/m/Y')),

                    Placeholder::make('car_due_date')
                        ->label('Due date')
                        ->content(fn($record)=>Carbon::parse($record->car_due_date)->format('d/m/Y')),

                    Placeholder::make('car_desc')
                        ->label('Description')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->car_desc),

                    Placeholder::make('hazard_level_id')
                        ->label('Hazard level')
                        ->content(fn ($record) => optional ($record->hazardLevel)->level_name ),

                    Placeholder::make('hazard_type_id')
                        ->label('Hazard type')
                        ->content(fn ($record) => optional ($record->hazardType)->type_name ),

                    Placeholder::make('status')
                        ->label('Status')
                        ->content(fn ($record) =>
                    ucfirst(str_replace('_', ' ', $record->status))),

                    View::make('components.car-reports-view-image')
                        ->label('Before Image')
                        ->viewData([
                            'path' => $this->getRecord()->img_before,
                        ])->columnSpan(2),

                    Placeholder::make('responsible_dept_id')
                        ->label('Reported to')
                        ->content(fn ($record) => optional ($record->responsible)->dept_name ),
                ])->columns('5'),

                Section::make('CAR Responses')
                ->visible(fn($record) => $record->carResponse !== null)
                ->schema([
                    View::make('components.car-responses-view-image')
                        ->label('After Image')
                        ->viewData([
                            'path' => $this->getRecord()->carResponse?->img_after,
                        ])
                        ->columnSpanFull(),

                        Placeholder::make('cause')
                        ->label('Cause')
                        ->columnSpan(2)
                        ->content(fn ($record) => $record->carResponse?->cause ),

                        Placeholder::make('status')
                        ->label('Status')
                        ->content(fn ($record) =>
                        ucfirst(str_replace('_', ' ', $record->carResponse?->status))),

                        Placeholder::make('created_by')
                        ->label('Created by')
                        ->content(fn($record)=>optional($record->carResponse?->createdResponse)->FullName),

                        Placeholder::make('temp_desc')
                        ->label('Temporary action')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->carResponse?->temp_desc ? $record->carResponse?->temp_desc : '-'),

                        Placeholder::make('temp_due_date')
                        ->label('Due date')
                        ->content(fn ($record) => $record->carResponse?->temp_due_date
                                    ? Carbon::parse($record->carResponse?->temp_due_date)->format('d/m/Y')
                                    : '-'),

                        Placeholder::make('temp_responsible')
                        ->label('Responsible')
                        ->content(fn($record)=>optional($record->carResponse?->tempResponsible)->FullName ?
                        ($record->carResponse?->tempResponsible)->FullName : '-'),

                        Placeholder::make('perm_desc')
                        ->label('Permanent action')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->carResponse?->perm_desc ? $record->carResponse?->perm_desc : '-'),


                        Placeholder::make('perm_due_date')
                        ->label('Due date')
                        ->content(fn ($record) => $record->carResponse?->perm_due_date
                                    ? Carbon::parse($record->carResponse?->perm_due_date)->format('d/m/Y')
                                    : '-'),

                        Placeholder::make('perm_responsible')
                        ->label('Responsible')
                        ->content(fn($record)=>optional($record->carResponse?->permResponsible)->FullName ?
                        ($record->carResponse?->permResponsible)->FullName : '-'),

                        Placeholder::make('preventive')
                        ->label('Preventive action')
                        ->columnSpanFull()
                        ->content(fn($record)=>$record->carResponse?->preventive),

                    ])->columns(4)
                    ->collapsed(),

            ]);
    }
}
