<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommonNotification extends Notification
{
    use Queueable;
    private $notify_params;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->notify_params = $params;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->notify_params['email_subject'])
                    ->line($this->notify_params['introduction_line'])
                    ->line($this->notify_params['email_text'])
                    ->markdown($this->notify_params['email_blade'], ['actionText' => $this->notify_params['action_text'], 'actionUrl' => $this->notify_params['action_url']])
                    ->line("Regards, " . env('APP_NAME'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
