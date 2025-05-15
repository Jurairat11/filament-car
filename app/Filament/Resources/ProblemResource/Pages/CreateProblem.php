<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use App\Filament\Resources\ProblemResource;
use Filament\Actions;
use illuminate\Support\Facades\Auth;
use App\Models\Problem;
use Filament\Resources\Pages\CreateRecord;

class CreateProblem extends CreateRecord
{
    protected static string $resource = ProblemResource::class;
    protected ?string $generatedProbId = null;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Problem created';
    }

    public function mount(): void
    {
        parent::mount();

        // Generate prob_id ตอนเปิดหน้า
        $this->generatedProbId = Problem::generateNextProbId();

        // fill ค่าเข้า form
        $this->form->fill([
            'prob_id' => $this->generatedProbId,
            'user_id' => Auth::user()?->FullName,
            'dept_id' => Auth::user()?->dept_id,
            'prob_date' => now(),
            'status' => 'new'
        ]);

    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prob_id'] = $this->generatedProbId ?? Problem::generateNextProbId();
        return $data;
    }

}
