<?php

namespace App\Observers;

use App\Models\Problem;
use App\Models\User;
use Filament\Notifications\Notification;

class ProblemObserver
{
    /**
     * Handle the Problem "created" event.
     */
    public function created(Problem $problem): void
    {
        User::role('Safety')->get()
        ->each(function ($user) use ($problem) {
            Notification::make()
                ->icon('heroicon-o-exclamation-circle')
                ->iconColor('warning')
                ->title('New problem reported')
                ->body("Problem ID: {$problem->prob_id}")
                ->sendToDatabase($user);
        });
    }

    /**
     * Handle the Problem "updated" event.
     */
    public function updated(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "deleted" event.
     */
    public function deleted(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "restored" event.
     */
    public function restored(Problem $problem): void
    {
        //
    }

    /**
     * Handle the Problem "force deleted" event.
     */
    public function forceDeleted(Problem $problem): void
    {
        //
    }
}
