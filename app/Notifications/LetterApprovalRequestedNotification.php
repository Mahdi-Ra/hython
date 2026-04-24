<?php

namespace App\Notifications;

use App\Models\LetterApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterApprovalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LetterApproval $approval
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $letter = $this->approval->letter;

        return [
            'type' => 'letter_approval_requested',
            'title' => 'درخواست تایید نامه',
            'body' => sprintf(
                '%s تایید نامه «%s» را از شما درخواست کرده است.',
                $this->approval->requestedBy?->name ?? 'کاربر',
                $letter->subject ?? '—'
            ),
            'body_short' => 'درخواست تایید ' . ($letter->reference_number ?? 'نامه'),
            'action_url' => route('letters.view', $letter),
            'action_label' => 'بررسی نامه',
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('درخواست تایید نامه')
            ->line('یک نامه برای تایید به شما ارسال شده است.')
            ->line('شماره دبیرخانه: ' . ($this->approval->letter->reference_number ?? '—'))
            ->action('بررسی نامه', route('letters.view', $this->approval->letter));
    }
}
