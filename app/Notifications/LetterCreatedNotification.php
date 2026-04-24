<?php

namespace App\Notifications;

use App\Models\Letter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Letter $letter
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $subject = $this->letter->subject ?? '—';
        $reference = $this->letter->reference_number ?? '—';

        return [
            'type' => 'letter_created',
            'title' => 'نامه جدید',
            'body' => sprintf('نامه «%s» با شماره %s برای دپارتمان شما ثبت شد.', $subject, $reference),
            'body_short' => 'نامه ' . $reference,
            'action_url' => route('letters.view', $this->letter),
            'action_label' => 'مشاهده نامه',
            'letter_id' => $this->letter->id,
            'letter_uuid' => $this->letter->uuid,
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('letters.view', $this->letter);

        return (new MailMessage)
            ->subject('نامه جدید ثبت شد')
            ->line('نامه جدیدی برای شما ثبت شده است.')
            ->line('شماره دبیرخانه: ' . ($this->letter->reference_number ?? '—'))
            ->action('مشاهده نامه', $url);
    }
}
