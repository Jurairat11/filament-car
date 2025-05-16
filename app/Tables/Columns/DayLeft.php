<?php

namespace App\Tables\Columns;

use Carbon\Carbon;
use Filament\Tables\Columns\Column;

class DayLeft extends Column
{
    protected string $view = 'tables.columns.day-left';

    public function getRemainingDays(): float|string
    {
        $dueDate = Carbon::parse($this->getRecord()->car_due_date);
        $today = Carbon::today();
        $carStatus = $this->getRecord()->status;
        $closeDate = Carbon::parse($this->getRecord()->close_car_date)->format('d/m/Y');

        if (in_array($carStatus, ['closed', 'pending_review'])) {
            return 'completed at: ' . $closeDate;
        }

        return $today->diffInDays($dueDate, false); // false = คืนค่าติดลบถ้าเลยกำหนด
    }


}
