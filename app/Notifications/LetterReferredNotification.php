<?php

namespace App\Notifications;

use App\Models\LetterReferral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LetterReferredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LetterReferral $referral
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * برای کانال دیتابیس (نمایش در پنل / نوتیفیکیشن‌ها).
     * متن و لینک فارسی و سازگار با RTL.
     */
    public function toArray(object $notifiable): array
    {
        $letter = $this->referral->letter;
        $fromUser = $this->referral->fromUser;
        $subject = $letter->subject;
        $dueDate = $letter->due_date?->format('Y/m/d');

        return [
            'type' => 'letter_referred',
            'title' => 'ارجاع نامه',
            'body' => sprintf(
                'نامه «%s» از طرف %s به شما ارجاع شده است.',
                $subject,
                $fromUser->name
            ),
            'body_short' => 'ارجاع نامه: ' . \Illuminate\Support\Str::limit($subject, 40),
            'action_url' => url('/admin/letters/' . $letter->uuid),
            'action_label' => 'مشاهده نامه',
            'letter_id' => $letter->id,
            'letter_uuid' => $letter->uuid,
            'referral_id' => $this->referral->id,
            'from_user_id' => $this->referral->from_user_id,
            'due_date' => $dueDate,
            'rtl' => true,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/admin/letters/' . $this->referral->letter->uuid);
        return (new MailMessage)
            ->subject('ارجاع نامه جدید')
            ->line('نامه‌ای به شما ارجاع شده است.')
            ->action('مشاهده نامه', $url);
    }
}
