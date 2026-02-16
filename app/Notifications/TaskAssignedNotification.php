<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * برای کانال دیتابیس؛ متن و لینک فارسی و RTL.
     */
    public function toArray(object $notifiable): array
    {
        $letter = $this->task->letter;
        $letterSubject = $letter->subject ?? '—';
        $dueDate = $this->task->due_date?->format('Y/m/d');

        return [
            'type' => 'task_assigned',
            'title' => 'محول شدن وظیفه',
            'body' => sprintf(
                'وظیفه «%s» به شما محول شده است. نامه: %s',
                $this->task->title,
                \Illuminate\Support\Str::limit($letterSubject, 30)
            ),
            'body_short' => 'وظیفه: ' . \Illuminate\Support\Str::limit($this->task->title, 40),
            'action_url' => url('/admin/tasks/' . $this->task->uuid),
            'action_label' => 'مشاهده وظیفه',
            'task_id' => $this->task->id,
            'task_uuid' => $this->task->uuid,
            'letter_id' => $this->task->letter_id,
            'due_date' => $dueDate,
            'priority' => $this->task->priority,
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/admin/tasks/' . $this->task->uuid);
        return (new MailMessage)
            ->subject('وظیفه جدید محول شده')
            ->line('وظیفه‌ای به شما محول شده است.')
            ->action('مشاهده وظیفه', $url);
    }
}
