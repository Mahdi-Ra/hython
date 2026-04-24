<?php

namespace App\Notifications;

use App\Models\Letter;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowedRecordUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public object $record,
        public string $title,
        public string $body
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'follow_activity',
            'title' => $this->title,
            'body' => $this->body,
            'body_short' => $this->body,
            'action_url' => $this->actionUrl(),
            'action_label' => 'مشاهده',
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->body)
            ->action('مشاهده', $this->actionUrl());
    }

    private function actionUrl(): string
    {
        if ($this->record instanceof Task) {
            return route('tasks.show', $this->record);
        }

        return route('letters.view', $this->record);
    }
}
