<?php

namespace App\Notifications;

use App\Models\LetterApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterApprovalDecisionNotification extends Notification implements ShouldQueue
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
            'type' => 'letter_approval_decision',
            'title' => 'نتیجه تایید نامه',
            'body' => sprintf(
                'درخواست تایید نامه «%s» توسط %s %s.',
                $letter->subject ?? '—',
                $this->approval->approver?->name ?? 'مسئول تایید',
                $this->approval->status === LetterApproval::STATUS_APPROVED ? 'تایید شد' : 'رد شد'
            ),
            'body_short' => 'نتیجه تایید ' . ($letter->reference_number ?? 'نامه'),
            'action_url' => route('letters.view', $letter),
            'action_label' => 'مشاهده نامه',
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('نتیجه تایید نامه')
            ->line('نتیجه درخواست تایید نامه ثبت شد.')
            ->line('وضعیت: ' . LetterApproval::statusLabel($this->approval->status))
            ->action('مشاهده نامه', route('letters.view', $this->approval->letter));
    }
}
