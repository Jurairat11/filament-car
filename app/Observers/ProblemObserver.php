<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Problem;
use Illuminate\Support\Str;
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

        $data = ['prob_id' => $problem->prob_id ?? '-',
                'prob_desc'=> $problem->prob_desc ?? '-',
                'user_id' => $problem->user->emp_id];

                $txtTitle = "รายงานปัญหาใหม่";

         // create connector instance
        $connector = new \Sebbmyr\Teams\TeamsConnector(env('MSTEAM_API'));
        // // create card
        // $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $data['title'], 'text' => $data['description']]);

        // create a custom card
        $card  = new \Sebbmyr\Teams\Cards\CustomCard("พนักงาน " . Str::upper($data['user_id']), "หัวข้อ: " . $txtTitle);
        // add information
        $card->setColor('01BC36')
            ->addFacts('รายละเอียด', ['รหัสปัญหา ' => $data['prob_id'], 'เพิ่มเติม' => $data['prob_desc']])
            ->addAction('Visit Issue', route('filament.admin.resources.problems.view', $problem));
        // send card via connector
        $connector->send($card);
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
