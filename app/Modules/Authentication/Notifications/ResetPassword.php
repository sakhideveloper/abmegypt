<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification 
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected readonly ?int $token,
        protected readonly string $email,
        protected readonly string $first_name
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expires = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->markdown('mails.forgot-password', [
                'title' => 'Reset Password Notification',
                'token' => $this->token,
                'name' => $this->first_name
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}