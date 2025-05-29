<?php

namespace App\Filament\Resources\CarResponsesResource\Pages;

use Filament\Actions;
use App\Models\Car_report;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CarResponsesResource;
use App\Models\Car_responses;

class CreateCarResponses extends CreateRecord
{
    protected static string $resource = CarResponsesResource::class;
    protected static ?string $title = 'Create CAR Responses';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'CAR response created';
    }

    public function mount(): void
    {
        parent::mount();

        // fill ค่าเข้า form
        $this->form->fill([
            'status' => 'draft',
            //'temp_due_date' => now(),
            // 'perm_due_date' => now(),
            'temp_status' => 'finished',
            'perm_status' => 'on process',
            'created_by' => Auth::user()->id,
        ]);

    }

    // protected function afterCreate(): void
    // {
    //     // เช็คว่าฟอร์มมี temp_desc หรือไม่
    //     if ($this->record->temp_desc) {
    //         Car_responses::where('id', $this->record->temp_desc)
    //             ->update([
    //                 'temp_status' => 'finished',
    //             ]);
    //     }
    // }

}
