<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutomationTriggeredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public string $actionLabel = 'مشاهده مورد'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'automation_triggered',
            'title' => $this->title,
            'body' => $this->body,
            'body_short' => $this->title,
            'action_url' => $this->actionUrl,
            'action_label' => $this->actionLabel,
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->body);

        if ($this->actionUrl) {
            $mail->action($this->actionLabel, $this->actionUrl);
        }

        return $mail;
    }
}
