<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Problem;
use Filament\Forms\Form;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Car_responses;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\Action;
use App\Filament\Resources\CarReportResource;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\Psr7\Request;

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
            ->modalHeading("Report CAR")
            ->modalDescription('You are about to report the car issue to the responsible department.')
            ->modalSubmitActionLabel('OK')
            ->visible(fn ($record) =>
                        Auth::user()?->hasAnyRole(['Safety','Admin']) && $record->status === 'draft'
                    )
            ->action(function($record, array $data) {

                Notification::make()
                ->title('Reported successfully')
                ->success()
                ->send();

                $record->update([
                    'status' => 'reported',

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

            $data = [
                'car_no' => $this->record->car_no ?? '-',
                'car_desc' => $this->record->car_desc ?? '-',
                'responsible_dept_id' => $this->record->responsible->dept_name?? '-',
                'created_by' => $this->record->users->emp_id?? '-',
            ];

            $txtTitle = "รายงานใบ CAR ใหม่";

            // create connector instance
            $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
            // // create card
            // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

            // create a custom card
            $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
            // add information
            $card->setColor('01BC36')
                ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_no'], 'ความไม่ปลอดภัย' => $data['car_desc'],
                'หน่วยงานผู้รับผิดชอบ' => $data['responsible_dept_id']])
                ->addAction('Visit Issue', route('filament.admin.pages.department-car-alert', $this->record));
            // send card via connector
            $connector->send($card);

        }),

            Action::make('Reopen')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading("Reopen CAR")
            ->modalDescription('You are about to reopen the car report.')
            ->modalSubmitActionLabel('OK')
            ->form([
                Textarea::make('reopen_car_reason')
                        ->label('สาเหตุการออกใบ CAR ใหม่')
                        ->required()
                        ->autosize()
                        ->maxLength(500),
                ])
            ->visible(fn ($record) =>
            Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status === 'pending_review')

            ->action(function ( $record, array $data) {

                $record->update([
                    'status' => 'reopened',
                    'reopen_car_reason' => $data['reopen_car_reason'],
                ]);

                //$this->record->update(['status' => 'reopened']);

                if ($this->record->problem_id) {
                        Problem::where('id', $this->record->problem_id)
                            ->update(['status' => 'accepted']);
                    }

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
                        ->body("The resolution for CAR no: {$this->record->car_no} was not accepted. A new CAR will be created.")
                        ->sendToDatabase($user);
                }

                 // ส่งค่าผ่าน query string
                return redirect()->route('filament.admin.resources.car-reports.create', [
                    'problem_id'           => $this->record->problem_id,
                    'dept_id'              => $this->record->dept_id,
                    'sec_id'               => $this->record->sec_id,
                    'car_date'             => $this->record->car_date,
                    'car_due_date'         => $this->record->car_due_date,
                    'equipment'            => $this->record->equipment,
                    'place_id'             => $this->record->place_id,
                    'hazard_source_id'     => $this->record->hazard_source_id,
                    'car_desc'             => $this->record->car_desc,
                    'hazard_level_id'      => $this->record->hazard_level_id,
                    'hazard_type_id'       => $this->record->hazard_type_id,
                    'img_before_path'      => $this->record->img_before_path,
                    'responsible_dept_id'  => $this->record->responsible_dept_id,
                    'created_by'           => $this->record->created_by,
                    'parent_car_id'        => $this->record->id, //ชี้กลับไปยัง CAR แม่
                     //ชึ้ไปยัง CAR ลูกที่ตามมา
                ]);
            }),

            Action::make('Close')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading("Close CAR")
            ->modalDescription('You are about to close the car report.')
            ->modalSubmitActionLabel('OK')
            ->visible(fn ($record) =>
                Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status === 'pending_review')
                ->action(function () {

                    Notification::make()
                    ->title('The CAR has already been closed')
                    ->success()
                    ->send();

                    // ปิดใบปัจจุบัน
                    $this->record->update(['status' => 'closed']);
                    $this->record->update(['close_car_date' => today()]);

                    // ปิดใบ CAR ก่อนหน้า
                    if ($this->record->parent_car_id) {
                        $this->record->parent()->update(['status' => 'closed']);
                    }

                    //ปิดปัญหาต้นทาง
                    if ($this->record->problem_id) {
                        Problem::where('id', $this->record->problem_id)
                            ->update(['status' => 'closed']);
                    }

                    // ปิด responses ที่เกี่ยวข้อง
                    $id = $this->record->id;
                    Car_responses::where('car_id', $id)
                    ->update(['status' => 'closed']);
                    //dd($this->record->id);

                    $parent_car_id = $this->record->parent_car_id;
                    Car_responses::where('car_id',$parent_car_id)->update(['status'=> 'closed']);
                    //dd($this->record->parent_car_id);

                    //แจ้งพนักงานผู้แจ้งปัญหา
                    $problem = Problem::find($this->record->problem_id);

                    if ($problem && $problem->user_id) {
                        $employee = User::find($problem->user_id);

                        if ($employee) {
                            Notification::make()
                                ->icon('heroicon-o-check-circle')
                                ->iconColor('success')
                                ->title('Your issue has been solved')
                                ->body("Your Problem ID: {$problem->prob_id} has been resolved.")
                                ->sendToDatabase($employee);
                        }
                    }

                    // Notify department
                    $users = User::role('User')
                        ->where('dept_id', $this->record->responsible_dept_id)
                        ->get();

                    foreach ($users as $user) {
                        Notification::make()
                            ->icon('heroicon-o-check-circle')
                            ->iconColor('success')
                            ->title('P-CAR Completed')
                            ->body("CAR no: {$this->record->car_no} has been completed.")
                            ->sendToDatabase($user);

                    }
                    $data = [
                    'car_no' => $this->record->car_no ?? '-',
                    'close_car_date' => $this->record->close_car_date ?? '-',
                    'created_by' => $this->record->users->emp_id?? '-',
                    ];
                    $txtTitle = "ปิดใบ CAR";

                    // create connector instance
                    $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
                    // // create card
                    // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

                    // create a custom card
                    $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['created_by']), "หัวข้อ: " . $txtTitle);
                    // add information
                    $card->setColor('01BC36')
                        ->addFacts('รายละเอียด', ['เลขที่ CAR ' => $data['car_no'], 'วันที่เสร็จสิ้น' => $data['close_car_date']->format('d/m/Y')])
                        ->addAction('Visit Issue', route('filament.admin.resources.car-reports.view', $this->record));
                    // send card via connector
                    $connector->send($card);
            }),

            Action::make('Reopen CAR')
                ->label('Reopen CAR')
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) =>
                    Auth::user()?->hasAnyRole(['Admin', 'Safety']) && $record->status === 'reopened')
                ->requiresConfirmation()
                ->modalHeading("Reopen CAR")
                ->modalDescription('You are about to reopen the car report.')
                ->modalSubmitActionLabel('OK')
                ->disabled(fn ($record) => $record->followed_car_id !== null)
                ->action(function () {
                    return redirect()->route('filament.admin.resources.car-reports.create', [
                        'problem_id'          => $this->record->problem_id, //เก็บ problem_id ของอันก่อนหน้า เพื่อสร้าง CAR ใหม่
                        'dept_id'             => $this->record->dept_id,
                        'sec_id'              => $this->record->sec_id,
                        'car_date'            => $this->record->car_date,
                        'car_due_date'        => $this->record->car_due_date,
                        'equipment'            => $this->record->equipment,
                        'place_id'             => $this->record->place_id,
                        'hazard_source_id'     => $this->record->hazard_source_id,
                        'car_desc'            => $this->record->car_desc,
                        'hazard_level_id'     => $this->record->hazard_level_id,
                        'hazard_type_id'      => $this->record->hazard_type_id,
                        'img_before_path'     => $this->record->img_before_path,
                        'created_by'          => $this->record->created_by,
                        'responsible_dept_id' => $this->record->responsible_dept_id,
                        'parent_car_id'       => $this->record->id,
                    ]);


                }),

                Action::make('print_car')
                ->label('Download CAR')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn ($record) =>
                    Auth::user()?->hasAnyRole(['Admin', 'Safety']) && ($record->status === 'closed'|| $record->status === 'reopened'))
                ->action(function () {
                    // Notification::make()
                    //     ->title('Downloading CAR')
                    //     ->body('The CAR report is being downloaded.')
                    //     ->success()
                    //     ->send();

                        $JASPER_SERVER = env('JASPER_SERVER', 'http://192.168.20.16:8080');
                        $JASPER_USER = env('JASPER_USER', 'jasperadmin');
                        $JASPER_PASSWORD = env('JASPER_PASSWORD', 'jasperadmin');

                        try {
                            session_start();
                            $jar = new SessionCookieJar('CookieJar', true);
                            $urlJasper = $JASPER_SERVER . "/jasperserver/rest_v2/login?j_username=" . $JASPER_USER . "&j_password=" . $JASPER_PASSWORD;
                            $client = new Client(['cookies' => $jar]);
                            $request = new Request('GET', $urlJasper, []);
                            $response = $client->sendAsync($request)->wait();
                            if ($response->getStatusCode() != 200) {
                                return Notification::make()
                                    ->danger()
                                    ->title('เกิดข้อผิดพลาด')
                                    ->body('ไม่สามารถเชื่อมต่อ Server Report ได้!')
                                    ->send();
                            }

                            $urlReport = $JASPER_SERVER . "/jasperserver/rest_v2/reports/car/car_report.pdf?ParmID=".$this->record->id;
                            $client = new Client(['cookies' => $jar]);
                            $request = new Request('GET', $urlReport);
                            $response = $client->sendAsync($request)->wait();
                        } catch (\Exception $e) {
                            return Notification::make()
                                ->danger()
                                ->title('เกิดข้อผิดพลาด')
                                ->body($e)
                                ->send();
                        }

                        if ($response->getStatusCode() != 200) {
                            return Notification::make()
                                ->danger()
                                ->title('เกิดข้อผิดพลาด')
                                ->body('User นี้ไม่สามารถเชื่อมต่อ Server Report ได้!')
                                ->send();
                        }

                        return response()->streamDownload(function () use ($response) {
                            echo $response->getBody();
                        }, "Car_Report_{$this->record->id}.pdf", [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="Car_Report.pdf"',
                        ]);
                })
        ];
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([
                Section::make('Problem Information')
                ->visible(fn($record) => $record->problem !== null && Auth::user()?->hasAnyRole(['Admin', 'Safety']))
                ->schema([
                    Placeholder::make('prob_id')
                        ->label('Problem ID')
                        ->content(fn ($record) => $record->problem?->prob_id),

                    Placeholder::make('title')
                        ->label('เรื่อง')
                        ->content(fn ($record) => $record->problem?->title),

                    Placeholder::make('place')
                        ->label('สถานที่ที่พบอันตราย')
                        ->content(fn ($record) => $record->problem?->place),

                    Placeholder::make('user_id')
                        ->label('ผู้แจ้งอันตราย')
                        ->content(fn ($record) => optional($record->problem?->user)->FullName),

                    Placeholder::make('dept_id')
                        ->label('แผนกผู้แจ้ง')
                        ->content(fn ($record) => optional($record->problem?->department)->dept_name),

                    Placeholder::make('prob_desc')
                        ->label('รายละเอียดอันตราย')
                        ->content(fn ($record) => $record->problem?->prob_desc)
                        ->columnSpanFull(),
                ])
                ->collapsed()
                ->columns(5),

                Section::make('Problem Information')
                ->visible(fn($record) => $record->problem !== null && Auth::user()?->hasAnyRole(['User']))
                ->schema([
                    Placeholder::make('prob_id')
                        ->label('Problem ID')
                        ->content(fn ($record) => $record->problem?->prob_id),

                    Placeholder::make('title')
                        ->label('เรื่อง')
                        ->content(fn ($record) => $record->problem?->title),

                    Placeholder::make('place')
                        ->label('สถานที่ที่พบอันตราย')
                        ->content(fn ($record) => $record->problem?->place),

                    Placeholder::make('prob_desc')
                        ->label('รายละเอียดอันตราย')
                        ->content(fn ($record) => $record->problem?->prob_desc),
                ])
                ->collapsed()
                ->columns(4),


                Section::make('CAR Information')
                ->description(fn ($livewire) =>
                    'CAR No: ' . ($livewire->form->getRawState()['car_no'] ?? '')
                )
                ->schema([
                    // Placeholder::make('problem_id')
                    //     ->label('Problem ID')
                    //     ->content(fn($record)=>optional($record->problem)->prob_id),

                    Placeholder::make('dept_id')
                        ->label('แผนก')
                        ->content(fn ($record) => optional ($record->department)->dept_name ),

                    Placeholder::make('sec_id')
                        ->label('ส่วนงาน')
                        ->content(fn($record)=>optional($record->section)->sec_name),

                    Placeholder::make('car_date')
                        ->label('วันที่สร้าง CAR')
                        ->content(fn($record)=>Carbon::parse($record->car_date)->format('d/m/Y')),

                    Placeholder::make('car_due_date')
                        ->label('วันที่ครบกำหนดแก้ไข')
                        ->content(fn($record)=>Carbon::parse($record->car_due_date)->format('d/m/Y')),

                    Placeholder::make('hazard_source_id')
                        ->label('แหล่งที่มาของอันตราย')
                        ->content(fn ($record) => optional ($record->hazardSource)->source_name),

                    Placeholder::make('place_id')
                        ->label('สถานที่ที่พบอันตราย')
                        ->content(fn ($record) => optional ($record->Place)->place_name),

                    Placeholder::make('equipment')
                        ->label('เครื่องจักร/สิ่งของ')
                        ->content(fn($record)=>$record->equipment),

                    Placeholder::make('responsible_dept_id')
                        ->label('แผนกผู้รับผิดชอบ')
                        ->content(fn ($record) => optional ($record->responsible)->dept_name ),

                    Placeholder::make('car_desc')
                        ->label('รายละเอียดความไม่ปลอดภัย')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->car_desc),

                    Placeholder::make('hazard_level_id')
                        ->label('ระดับความอันตราย')
                        ->content(fn ($record) => optional ($record->hazardLevel)->level_name ),

                    Placeholder::make('hazard_type_id')
                        ->label('ประเภทของอันตราย')
                        ->content(fn ($record) => optional ($record->hazardType)->type_name ),

                    View::make('components.car-reports-view-image')
                        ->label('รูปภาพอันตราย(ก่อน)')
                        ->viewData([
                            'path' => $this->getRecord()->img_before_path,
                        ])->columnSpan(2),

                    Placeholder::make('reopen_car_reason')
                        ->label('สาเหตุการออก CAR ใหม่')
                        ->content(fn ($record) => $record->reopen_car_reason ?? '-')
                        ->visible(fn ($record) => $record->status === 'reopened')
                        ->columns(2),

                ])->columns('4'),

                Section::make('CAR Responses')
                ->visible(fn($record) => $record->carResponse !== null)
                ->schema([
                    View::make('components.car-responses-view-image')
                        ->label('รูปภาพอันตราย (หลัง)')
                        ->viewData([
                            'path' => $this->getRecord()->carResponse?->img_after_path,
                        ])
                        ->columnSpanFull(),

                        Placeholder::make('cause')
                        ->label('สาเหตุ')
                        ->columnSpan(2)
                        ->content(fn ($record) => $record->carResponse?->cause ),

                        Placeholder::make('status')
                        ->label('Status')
                        ->content(fn ($record) =>
                        ucfirst(str_replace('_', ' ', $record->carResponse?->status))),

                        Placeholder::make('created_by')
                        ->label('ผู้สร้าง')
                        ->content(fn($record)=>optional($record->carResponse?->createdResponse)->FullName),

                        Placeholder::make('temp_desc')
                        ->label('มาตรการแก้ไขชั่วคราว')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->carResponse?->temp_desc ? $record->carResponse?->temp_desc : ''),

                        Placeholder::make('temp_due_date')
                        ->label('วันที่กำหนดเสร็จ')
                        ->content(fn ($record) => $record->carResponse?->temp_due_date
                                    ? Carbon::parse($record->carResponse?->temp_due_date)->format('d/m/Y')
                                    : '-'),

                        Placeholder::make('temp_responsible')
                        ->label('ผู้รับผิดชอบ')
                        ->content(fn($record)=>$record->carResponse?->temp_responsible ? $record->carResponse?->temp_responsible : ''),

                        Placeholder::make('perm_desc')
                        ->label('มาตรการแก้ไขถาวร')
                        ->columnSpan(2)
                        ->content(fn($record)=>$record->carResponse?->perm_desc ? $record->carResponse?->perm_desc : ''),


                        Placeholder::make('perm_due_date')
                        ->label('วันที่กำหนดเสร็จ')
                        ->content(fn ($record) => $record->carResponse?->perm_due_date
                                    ? Carbon::parse($record->carResponse?->perm_due_date)->format('d/m/Y')
                                    : '-'),

                        Placeholder::make('perm_responsible')
                        ->label('ผู้รับผิดชอบ')
                        ->content(fn($record)=>$record->carResponse?->perm_responsible ? $record->carResponse?->perm_responsible : ''),

                        Placeholder::make('preventive')
                        ->label('กำหนดมาตรการป้องกันการเกิดปัญหาซ้ำ')
                        ->columnSpanFull()
                        ->content(fn($record)=>$record->carResponse?->preventive),

                    ])->columns(4)
                    ->collapsed(),

            ]);
    }
}
