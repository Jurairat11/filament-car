<?php

namespace App\Tables\Columns;

use Carbon\Carbon;
use Filament\Tables\Columns\Column;

class DayLeft extends Column
{
    protected string $view = 'tables.columns.day-left';

    public function getRemainingDays()
    {
        $dueDate = Carbon::parse($this->getRecord()->car_due_date);
        $today = Carbon::today();

        return $today->diffInDays($dueDate, false); // false = คืนค่าติดลบถ้าเลยกำหนด
    }

}
