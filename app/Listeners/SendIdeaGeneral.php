<?php

namespace App\Listeners;

use App\Events\IdeaGeneral;
use App\Mail\GeneralMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendIdeaGeneral implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(IdeaGeneral $event): void
    {
        $project = $event->project->load([
            'projectStatus',
            'students.user',
            'professors.user',
        ]);

        $recipients = collect();

        foreach ($project->students as $student) {
            if ($student->user) {
                $recipients->push([
                    'email' => $student->user->email,
                    'name' => $student->name.' '.$student->last_name,
                    'role' => 'student',
                ]);
            }
        }

        foreach ($project->professors as $professors) {
            if ($professors->user) {
                $recipients->push([
                    'email' => $professors->user->email,
                    'name' => $professors->name.' '.$professors->last_name,
                    'role' => 'professors',
                ]);
            }
        }

        $recipients->unique('email')->each(function ($recipient) use ($project) {
            Log::info($recipient['email']);
            Mail::to($recipient['email'])
                ->send(new GeneralMail($project, $recipient));
        });
    }
}
