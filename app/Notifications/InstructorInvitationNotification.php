<?php

namespace App\Notifications;

use App\Models\InstructorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructorInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public InstructorInvitation $invitation,
        public string $token,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are invited to join GT Driving as an instructor')
            ->greeting('Instructor invitation')
            ->line(($this->invitation->inviter?->name ?? 'A GT Driving administrator').' invited you to join GT Driving as an instructor.')
            ->line('This single-use invitation expires '.$this->invitation->expires_at->timezone(config('app.timezone'))->format('j M Y, g:i A T').'.')
            ->action('Accept instructor invitation', route('instructor-invitations.show', $this->token))
            ->line('If you were not expecting this invitation, you can safely ignore this email.');
    }
}
