<?php

namespace App\Console\Commands;

use App\Mail\AssignmentDeadlineReminderMail;
use App\Models\AssignedTest;
use App\Models\AssignedTestStudent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAssignmentDeadlineReminders extends Command
{
    protected $signature   = 'assignments:send-reminders';
    protected $description = 'Send deadline reminder emails for assignments due within 24h or 1h';

    public function handle(): void
    {
        $now = now();

        // Find active assignments whose due_date is in the next 24h or 1h windows
        $assignments = AssignedTest::where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '>', $now)
            ->where('due_date', '<=', $now->copy()->addHours(25)) // fetch both windows in one query
            ->with(['institute', 'studentRecords.user'])
            ->get();

        $sent = 0;

        foreach ($assignments as $assignment) {
            $hoursLeft = (int) ceil($now->diffInHours($assignment->due_date, false));

            // Only send at the ~24h and ~1h marks (within a 1h tolerance window)
            $sendWindow = match(true) {
                $hoursLeft >= 23 && $hoursLeft <= 25 => 24,
                $hoursLeft >= 1  && $hoursLeft <= 2  => 1,
                default => null,
            };

            if (!$sendWindow) continue;

            // Only students who haven't completed yet
            $pending = $assignment->studentRecords
                ->whereNotIn('status', ['completed', 'skipped']);

            foreach ($pending as $record) {
                if (!$record->user) continue;

                Mail::to($record->user->email)->queue(
                    new AssignmentDeadlineReminderMail($record->user, $assignment, $sendWindow)
                );
                $sent++;
            }
        }

        $this->info("Sent {$sent} deadline reminder(s).");
    }
}
