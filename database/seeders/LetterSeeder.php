<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Letter;
use App\Models\LetterReferral;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class LetterSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('role', '!=', User::ROLE_ADMIN)->pluck('id')->toArray();
        $depts = Department::query()->pluck('id')->toArray();
        if (empty($users) || empty($depts)) {
            return;
        }

        $lettersData = [
            ['subject' => 'درخواست تأیید خرید تجهیزات اداری', 'type' => Letter::TYPE_INTERNAL, 'priority' => Letter::PRIORITY_HIGH, 'status' => Letter::STATUS_PENDING],
            ['subject' => 'گزارش ماهانه واحد فناوری اطلاعات', 'type' => Letter::TYPE_INTERNAL, 'priority' => Letter::PRIORITY_NORMAL, 'status' => Letter::STATUS_IN_PROGRESS],
            ['subject' => 'هماهنگی جلسه با طرف خارجی', 'type' => Letter::TYPE_EXTERNAL, 'priority' => Letter::PRIORITY_URGENT, 'status' => Letter::STATUS_PENDING],
            ['subject' => 'اصلاحات آیین‌نامه داخلی', 'type' => Letter::TYPE_INTERNAL, 'priority' => Letter::PRIORITY_NORMAL, 'status' => Letter::STATUS_DRAFT],
            ['subject' => 'پیشنهاد همکاری با شرکت الف', 'type' => Letter::TYPE_EXTERNAL, 'priority' => Letter::PRIORITY_HIGH, 'status' => Letter::STATUS_PENDING],
            ['subject' => 'درخواست مرخصی تیمی', 'type' => Letter::TYPE_INTERNAL, 'priority' => Letter::PRIORITY_LOW, 'status' => Letter::STATUS_COMPLETED],
            ['subject' => 'ابلاغ بخشنامه جدید', 'type' => Letter::TYPE_INTERNAL, 'priority' => Letter::PRIORITY_NORMAL, 'status' => Letter::STATUS_IN_PROGRESS],
            ['subject' => 'پیگیری قرارداد پیمانکاری', 'type' => Letter::TYPE_EXTERNAL, 'priority' => Letter::PRIORITY_URGENT, 'status' => Letter::STATUS_PENDING],
        ];

        $bodySample = 'متن نمونه نامه برای نمایش در دمو. این محتوا به زبان فارسی و راست‌به‌چپ است.';

        foreach ($lettersData as $i => $data) {
            $letter = Letter::query()->firstOrCreate(
                [
                    'subject' => $data['subject'],
                    'user_id' => $users[array_rand($users)],
                ],
                [
                    'department_id' => $depts[array_rand($depts)],
                    'type' => $data['type'],
                    'subject' => $data['subject'],
                    'body' => $bodySample,
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'due_date' => now()->addDays(rand(3, 14)),
                    'reference_number' => 'REF-' . (1000 + $i),
                ]
            );

            $this->addReferralsForLetter($letter, $users);
            $this->addTasksForLetter($letter, $users);
            $this->addCommentsForLetter($letter, $users);
            $this->addAttachmentsForLetter($letter, $users);
        }

        $this->addTaskComments($users);
    }

    private function addTaskComments(array $userIds): void
    {
        $task = Task::query()->first();
        if ($task && count($userIds) > 0) {
            Comment::query()->firstOrCreate(
                ['commentable_type' => Task::class, 'commentable_id' => $task->id, 'user_id' => $userIds[array_rand($userIds)]],
                ['body' => 'یادداشت نمونه برای این وظیفه.']
            );
        }
    }

    private function addReferralsForLetter(Letter $letter, array $userIds): void
    {
        $fromId = $letter->user_id;
        $others = array_filter($userIds, fn ($id) => $id != $fromId);
        if (count($others) < 2) {
            return;
        }
        $toId = $others[array_rand($others)];
        LetterReferral::query()->firstOrCreate(
            ['letter_id' => $letter->id, 'to_user_id' => $toId],
            [
                'from_user_id' => $fromId,
                'assigned_by_user_id' => $fromId,
                'status' => LetterReferral::STATUS_PENDING,
                'note' => 'ارجاع برای بررسی و اقدام.',
                'referred_at' => now()->subDays(rand(0, 3)),
            ]
        );
    }

    private function addTasksForLetter(Letter $letter, array $userIds): void
    {
        $creatorId = $letter->user_id;
        $assigneeId = $userIds[array_rand($userIds)];
        Task::query()->firstOrCreate(
            ['letter_id' => $letter->id, 'title' => 'پیگیری و پاسخ‌دهی به نامه'],
            [
                'assigned_to_user_id' => $assigneeId,
                'created_by_user_id' => $creatorId,
                'description' => 'وظیفه نمونه مرتبط با نامه.',
                'status' => Task::STATUS_PENDING,
                'priority' => $letter->priority === Letter::PRIORITY_URGENT ? Task::PRIORITY_HIGH : Task::PRIORITY_NORMAL,
                'due_date' => $letter->due_date,
            ]
        );
    }

    private function addCommentsForLetter(Letter $letter, array $userIds): void
    {
        $userId = $userIds[array_rand($userIds)];
        Comment::query()->firstOrCreate(
            ['commentable_type' => Letter::class, 'commentable_id' => $letter->id, 'user_id' => $userId],
            ['body' => 'یادداشت نمونه برای این نامه.']
        );
    }

    private function addAttachmentsForLetter(Letter $letter, array $userIds): void
    {
        $userId = $userIds[array_rand($userIds)];
        Attachment::query()->firstOrCreate(
            ['letter_id' => $letter->id, 'name' => 'سند-ضمیمه-نمونه.pdf'],
            [
                'user_id' => $userId,
                'path' => 'letter-attachments/demo-placeholder.pdf',
                'mime_type' => 'application/pdf',
                'size' => 1024,
            ]
        );
    }
}
