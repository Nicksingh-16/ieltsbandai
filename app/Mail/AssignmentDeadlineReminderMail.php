<?php

namespace App\Mail;

use App\Models\AssignedTest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssignmentDeadlineReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public AssignedTest $assignment,
        public int $hoursRemaining,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: \"{$this->assignment->title}\" due in {$this->hoursRemaining}h",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.assignment-deadline-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
