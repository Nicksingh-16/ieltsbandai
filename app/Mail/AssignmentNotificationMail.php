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

class AssignmentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public AssignedTest $assignment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Test Assigned: {$this->assignment->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.assignment-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
