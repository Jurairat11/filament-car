<?php

namespace App\Observers;

use App\Models\Car_report;
use Illuminate\Support\Str;

class Car_reportObserver
{
    /**
     * Handle the Car_report "created" event.
     */
    public function created(Car_report $car_report): void
    {
        //
    }

    /**
     * Handle the Car_report "updated" event.
     */
    public function updated(Car_report $car_report): void
    {
        //
    }

    /**
     * Handle the Car_report "deleted" event.
     */
    public function deleted(Car_report $car_report): void
    {
        //
    }

    /**
     * Handle the Car_report "restored" event.
     */
    public function restored(Car_report $car_report): void
    {
        //
    }

    /**
     * Handle the Car_report "force deleted" event.
     */
    public function forceDeleted(Car_report $car_report): void
    {
        //
    }

}

