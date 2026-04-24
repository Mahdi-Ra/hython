<?php

namespace App\Notifications;

use App\Models\Task;
use App\Support\JalaliDate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $kind
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $isOverdue = $this->kind === 'overdue';

        return [
            'type' => 'task_reminder',
            'title' => $isOverdue ? 'پیگیری وظیفه معوق' : 'یادآوری سررسید وظیفه',
            'body' => $isOverdue
                ? sprintf('وظیفه «%s» از مهلت تعیین‌شده عبور کرده است.', $this->task->title)
                : sprintf('وظیفه «%s» نزدیک به موعد انجام است.', $this->task->title),
            'body_short' => $isOverdue ? 'وظیفه معوق' : 'سررسید وظیفه',
            'action_url' => route('tasks.show', $this->task),
            'action_label' => 'مشاهده وظیفه',
            'task_id' => $this->task->id,
            'task_uuid' => $this->task->uuid,
            'reminder_kind' => $this->kind,
            'reminder_date' => now()->toDateString(),
            'due_date' => JalaliDate::format($this->task->due_date, false, ''),
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isOverdue = $this->kind === 'overdue';

        return (new MailMessage)
            ->subject($isOverdue ? 'پیگیری وظیفه معوق' : 'یادآوری سررسید وظیفه')
            ->line($isOverdue
                ? 'این وظیفه از موعد انجام عبور کرده است.'
                : 'موعد انجام این وظیفه نزدیک است.')
            ->line('مهلت: ' . JalaliDate::format($this->task->due_date, false, '—'))
            ->action('مشاهده وظیفه', route('tasks.show', $this->task));
    }
}
