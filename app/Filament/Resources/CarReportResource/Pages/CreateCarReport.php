<?php

namespace App\Filament\Resources\CarReportResource\Pages;

use Carbon\Carbon;
use App\Models\Car_report;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CarReportResource;

class CreateCarReport extends CreateRecord
{
    protected static string $resource = CarReportResource::class;
    protected static ?string $title = 'Create CAR Report';
    public string $generatedCarNo;
    public function mount(): void
    {
        parent::mount();

        //$this->generatedCarNo = Car_report::generateNextCarNo(); // เรียกจาก Model

        $this->form->fill([
            //'car_no' => $this->generatedCarNo,
            'status' => 'draft',
            'problem_id'        => request()->get('problem_id'),
            'dept_id'           => request()->get('dept_id'),
            'sec_id'            => request()->get('sec_id'),
            'car_date'          => now(),
            'car_due_date'      => now(),
            'equipment'         => request()->get('equipment'),
            'place_id'          => request()->get('place_id'),
            'hazard_source_id'  => request()->get('hazard_source_id'),
            'car_desc'          => request()->get('car_desc'),
            'hazard_level_id'   => request()->get('hazard_level_id'),
            'hazard_type_id'    => request()->get('hazard_type_id'),
            'img_before_path'   => request()->get('img_before_path'),
            'created_by'        => Auth::user()?->id,
            'responsible_dept_id' => request()->get('responsible_dept_id'),
            'parent_car_id'       => request()->get('parent_car_id'),
            'responsible_group' => 'general'
        ]);
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['car_no'] = $this->generatedCarNo ?? Car_report::generateNextCarNo();
    //     $data['img_before'] = ImageHelper::convertToUrl($data['img_before_path'] ?? null);
    //     return $data;
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['car_no'] = $this->generatedCarNo ?? Car_report::generateCarNo();
        $data['img_before'] = ImageHelper::convertToUrl($data['img_before_path'] ?? null);
        return $data;
    }

    protected function afterCreate(): void
    {
        // เช็คว่าฟอร์มมี parent_car_id หรือไม่
        if ($this->record->parent_car_id) {
            Car_report::where('id', $this->record->parent_car_id)
                ->update([
                    'followed_car_id' => $this->record->id,
                ]);
        }

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'CAR created';
    }

}
