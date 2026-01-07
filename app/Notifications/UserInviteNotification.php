<?php

namespace App\Notifications;

use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Organisation $organisation, private readonly string $role, private readonly string $token)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url("/accept-invite/{$this->token}");

        return (new MailMessage)
            ->subject('You are invited to Lux OS')
            ->greeting('Hi there,')
            ->line("You've been invited to join {$this->organisation->trading_name ?? $this->organisation->name} as {$this->role}.")
            ->action('Accept invitation', $url)
            ->line('The link expires in 7 days.');
    }
}
