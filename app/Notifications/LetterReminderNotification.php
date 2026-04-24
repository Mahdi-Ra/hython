<?php

namespace App\Notifications;

use App\Models\Letter;
use App\Support\JalaliDate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Letter $letter,
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
            'type' => 'letter_reminder',
            'title' => $isOverdue ? 'پیگیری نامه معوق' : 'یادآوری سررسید نامه',
            'body' => $isOverdue
                ? sprintf('نامه %s با موضوع «%s» از مهلت تعیین‌شده عبور کرده است.', $this->letter->reference_number ?? '—', $this->letter->subject)
                : sprintf('نامه %s با موضوع «%s» نزدیک به موعد رسیدگی است.', $this->letter->reference_number ?? '—', $this->letter->subject),
            'body_short' => $isOverdue ? 'نامه معوق' : 'سررسید نامه',
            'action_url' => route('letters.view', $this->letter),
            'action_label' => 'مشاهده نامه',
            'letter_id' => $this->letter->id,
            'letter_uuid' => $this->letter->uuid,
            'reminder_kind' => $this->kind,
            'reminder_date' => now()->toDateString(),
            'due_date' => JalaliDate::format($this->letter->due_date, false, ''),
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isOverdue = $this->kind === 'overdue';

        return (new MailMessage)
            ->subject($isOverdue ? 'پیگیری نامه معوق' : 'یادآوری سررسید نامه')
            ->line($isOverdue
                ? 'این نامه از موعد رسیدگی عبور کرده است.'
                : 'موعد رسیدگی این نامه نزدیک است.')
            ->line('شماره دبیرخانه: ' . ($this->letter->reference_number ?? '—'))
            ->line('مهلت: ' . JalaliDate::format($this->letter->due_date, false, '—'))
            ->action('مشاهده نامه', route('letters.view', $this->letter));
    }
}
