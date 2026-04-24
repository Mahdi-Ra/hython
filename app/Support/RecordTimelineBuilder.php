<?php

namespace App\Support;

use App\Models\Letter;
use App\Models\LetterApproval;
use App\Models\LetterReferral;
use App\Models\Task;
use Illuminate\Support\Collection;

class RecordTimelineBuilder
{
    public static function forLetter(Letter $letter): Collection
    {
        $timeline = collect([
            [
                'at' => $letter->created_at,
                'title' => 'نامه ثبت شد',
                'description' => 'نامه توسط ' . ($letter->user?->name ?? 'کاربر') . ' در سیستم ثبت شد.',
                'kind' => 'created',
            ],
        ]);

        foreach ($letter->referrals as $referral) {
            $timeline->push([
                'at' => $referral->referred_at ?? $referral->created_at,
                'title' => 'ارجاع نامه',
                'description' => sprintf(
                    'از %s به %s - وضعیت: %s',
                    $referral->fromUser?->name ?? '—',
                    $referral->toUser?->name ?? '—',
                    LetterReferral::statusLabel($referral->status)
                ),
                'meta' => $referral->note,
                'response' => $referral->response_note,
                'kind' => 'referral',
            ]);
        }

        foreach ($letter->comments as $comment) {
            $timeline->push([
                'at' => $comment->created_at,
                'title' => 'یادداشت جدید',
                'description' => ($comment->user?->name ?? 'کاربر') . ' یک یادداشت ثبت کرد.',
                'meta' => $comment->body,
                'kind' => 'comment',
            ]);
        }

        foreach ($letter->approvals as $approval) {
            $timeline->push([
                'at' => $approval->requested_at ?? $approval->created_at,
                'title' => 'درخواست تایید',
                'description' => sprintf(
                    '%s نامه را برای تایید به %s ارسال کرد.',
                    $approval->requestedBy?->name ?? 'کاربر',
                    $approval->approver?->name ?? 'مسئول تایید'
                ),
                'meta' => $approval->request_note,
                'kind' => 'approval_request',
            ]);

            if (in_array($approval->status, [LetterApproval::STATUS_APPROVED, LetterApproval::STATUS_REJECTED], true)) {
                $timeline->push([
                    'at' => $approval->decided_at ?? $approval->updated_at,
                    'title' => $approval->status === LetterApproval::STATUS_APPROVED ? 'نامه تایید شد' : 'نامه رد شد',
                    'description' => sprintf(
                        '%s نتیجه تایید نامه را %s ثبت کرد.',
                        $approval->approver?->name ?? 'مسئول تایید',
                        $approval->status === LetterApproval::STATUS_APPROVED ? 'تایید' : 'رد'
                    ),
                    'meta' => $approval->decision_note,
                    'kind' => 'approval_decision',
                ]);
            }
        }

        foreach ($letter->auditLogs as $auditLog) {
            $oldStatus = data_get($auditLog->old_values, 'status');
            $newStatus = data_get($auditLog->new_values, 'status');
            if ($oldStatus !== $newStatus && $newStatus) {
                $timeline->push([
                    'at' => $auditLog->created_at,
                    'title' => 'تغییر وضعیت',
                    'description' => sprintf(
                        'وضعیت از %s به %s تغییر کرد.',
                        Letter::statusLabel($oldStatus),
                        Letter::statusLabel($newStatus)
                    ),
                    'meta' => $auditLog->user?->name ? 'توسط ' . $auditLog->user?->name : null,
                    'kind' => 'status',
                ]);
            }
        }

        return $timeline
            ->filter(fn (array $item) => filled($item['at'] ?? null))
            ->sortByDesc('at')
            ->values();
    }

    public static function forTask(Task $task): Collection
    {
        $timeline = collect([
            [
                'at' => $task->created_at,
                'title' => 'وظیفه ثبت شد',
                'description' => 'وظیفه توسط ' . ($task->createdBy?->name ?? 'کاربر') . ' ایجاد شد.',
                'kind' => 'created',
            ],
        ]);

        if ($task->letter) {
            $timeline->push([
                'at' => $task->created_at,
                'title' => 'اتصال به نامه',
                'description' => 'این وظیفه به نامه «' . ($task->letter->subject ?? '—') . '» متصل است.',
                'kind' => 'link',
            ]);
        }

        foreach ($task->comments as $comment) {
            $timeline->push([
                'at' => $comment->created_at,
                'title' => 'یادداشت جدید',
                'description' => ($comment->user?->name ?? 'کاربر') . ' یک یادداشت روی وظیفه ثبت کرد.',
                'meta' => $comment->body,
                'kind' => 'comment',
            ]);
        }

        foreach ($task->auditLogs as $auditLog) {
            $oldValues = $auditLog->old_values ?? [];
            $newValues = $auditLog->new_values ?? [];

            $oldStatus = data_get($oldValues, 'status');
            $newStatus = data_get($newValues, 'status');
            if ($oldStatus !== $newStatus && $newStatus) {
                $timeline->push([
                    'at' => $auditLog->created_at,
                    'title' => 'تغییر وضعیت',
                    'description' => sprintf(
                        'وضعیت از %s به %s تغییر کرد.',
                        Task::statusLabel($oldStatus),
                        Task::statusLabel($newStatus)
                    ),
                    'meta' => $auditLog->user?->name ? 'توسط ' . $auditLog->user?->name : null,
                    'kind' => 'status',
                ]);
            }

            $oldAssignee = data_get($oldValues, 'assigned_to_user_id') ?? data_get($oldValues, 'assigned_to');
            $newAssignee = data_get($newValues, 'assigned_to_user_id') ?? data_get($newValues, 'assigned_to');
            if ($oldAssignee !== $newAssignee && $newAssignee) {
                $timeline->push([
                    'at' => $auditLog->created_at,
                    'title' => 'تغییر مسئول',
                    'description' => 'مسئول وظیفه تغییر کرد.',
                    'meta' => $auditLog->user?->name ? 'توسط ' . $auditLog->user?->name : null,
                    'kind' => 'assignee',
                ]);
            }

            $oldDueDate = data_get($oldValues, 'due_date');
            $newDueDate = data_get($newValues, 'due_date');
            if ($oldDueDate !== $newDueDate && $newDueDate) {
                $timeline->push([
                    'at' => $auditLog->created_at,
                    'title' => 'تغییر مهلت',
                    'description' => 'مهلت وظیفه به ' . JalaliDate::format($newDueDate) . ' تغییر کرد.',
                    'meta' => $auditLog->user?->name ? 'توسط ' . $auditLog->user?->name : null,
                    'kind' => 'deadline',
                ]);
            }
        }

        return $timeline
            ->filter(fn (array $item) => filled($item['at'] ?? null))
            ->sortByDesc('at')
            ->values();
    }
}
