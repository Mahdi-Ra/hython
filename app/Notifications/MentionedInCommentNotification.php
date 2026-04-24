<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Letter;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MentionedInCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public object $record
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->recordLabel();

        return [
            'type' => 'comment_mention',
            'title' => 'منشن شدید',
            'body' => sprintf(
                '%s شما را در یادداشت %s «%s» منشن کرده است.',
                $this->comment->user?->name ?? 'کاربر',
                $label,
                $this->recordTitle()
            ),
            'body_short' => Str::limit($this->comment->body, 60),
            'action_url' => $this->actionUrl(),
            'action_label' => 'مشاهده',
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('منشن جدید در سیستم')
            ->line(sprintf(
                '%s شما را در یک یادداشت منشن کرده است.',
                $this->comment->user?->name ?? 'کاربر'
            ))
            ->line(Str::limit($this->comment->body, 120))
            ->action('مشاهده', $this->actionUrl());
    }

    private function recordLabel(): string
    {
        return $this->record instanceof Task ? 'وظیفه' : 'نامه';
    }

    private function recordTitle(): string
    {
        if ($this->record instanceof Task) {
            return $this->record->title;
        }

        return $this->record instanceof Letter ? ($this->record->subject ?? '—') : '—';
    }

    private function actionUrl(): string
    {
        if ($this->record instanceof Task) {
            return route('tasks.show', $this->record);
        }

        return route('letters.view', $this->record);
    }
}
