<?php

namespace App\Support;

use App\Models\Letter;
use App\Models\LetterReferral;
use App\Models\Task;
use App\Models\User;
use App\Notifications\LetterReminderNotification;
use App\Notifications\TaskReminderNotification;
use Illuminate\Support\Collection;

class DueReminderDispatcher
{
    public function dispatch(): array
    {
        $taskNotifications = 0;
        $letterNotifications = 0;

        $dueSoonTasks = Task::query()
            ->with(['assignedTo:id,name,email', 'createdBy:id,name,email'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', now()->addDay()->toDateString())
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->get();

        foreach ($dueSoonTasks as $task) {
            $taskNotifications += $this->notifyTaskRecipients($task, 'due_soon');
        }

        $overdueTasks = Task::query()
            ->with(['assignedTo:id,name,email', 'createdBy:id,name,email'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->get();

        foreach ($overdueTasks as $task) {
            $taskNotifications += $this->notifyTaskRecipients($task, 'overdue');
            app(AutomationEngine::class)->trigger('task_overdue', [
                'task' => $task,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'department_id' => $task->assignedTo?->department_id,
                'priority' => $task->priority,
                'actor_id' => $task->created_by_user_id,
            ]);
        }

        $dueSoonLetters = Letter::query()
            ->with(['user:id,name,email', 'department.users:id,name,email,role,department_id', 'referrals.toUser:id,name,email'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', now()->addDay()->toDateString())
            ->whereNotIn('status', [Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED])
            ->get();

        foreach ($dueSoonLetters as $letter) {
            $letterNotifications += $this->notifyLetterRecipients($letter, 'due_soon');
        }

        $overdueLetters = Letter::query()
            ->with(['user:id,name,email', 'department.users:id,name,email,role,department_id', 'referrals.toUser:id,name,email'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', [Letter::STATUS_COMPLETED, Letter::STATUS_ARCHIVED])
            ->get();

        foreach ($overdueLetters as $letter) {
            $letterNotifications += $this->notifyLetterRecipients($letter, 'overdue');
            app(AutomationEngine::class)->trigger('letter_overdue', [
                'letter' => $letter,
                'letter_id' => $letter->id,
                'letter_subject' => $letter->subject,
                'letter_reference' => $letter->reference_number,
                'department_id' => $letter->department_id,
                'department_name' => $letter->department?->name,
                'priority' => $letter->priority,
                'actor_id' => $letter->user_id,
            ]);
        }

        return [
            'tasks' => $taskNotifications,
            'letters' => $letterNotifications,
        ];
    }

    private function notifyTaskRecipients(Task $task, string $kind): int
    {
        $recipients = collect([$task->assignedTo, $kind === 'overdue' ? $task->createdBy : null])
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();

        $sent = 0;
        foreach ($recipients as $recipient) {
            if ($this->alreadySent($recipient, TaskReminderNotification::class, [
                'task_id' => $task->id,
                'reminder_kind' => $kind,
            ])) {
                continue;
            }

            $recipient->notify(new TaskReminderNotification($task, $kind));
            $sent++;
        }

        return $sent;
    }

    private function notifyLetterRecipients(Letter $letter, string $kind): int
    {
        $openReferralUsers = $letter->referrals
            ->whereIn('status', [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED])
            ->pluck('toUser');

        $departmentManagers = $kind === 'overdue'
            ? $letter->department?->users?->filter(fn ($user) => $user->role === User::ROLE_MANAGER)
            : collect();

        $recipients = collect([$letter->user])
            ->merge($openReferralUsers)
            ->merge($departmentManagers ?? collect())
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();

        $sent = 0;
        foreach ($recipients as $recipient) {
            if ($this->alreadySent($recipient, LetterReminderNotification::class, [
                'letter_id' => $letter->id,
                'reminder_kind' => $kind,
            ])) {
                continue;
            }

            $recipient->notify(new LetterReminderNotification($letter, $kind));
            $sent++;
        }

        return $sent;
    }

    private function alreadySent(User $user, string $notificationClass, array $conditions): bool
    {
        $notifications = $user->notifications()
            ->where('type', $notificationClass)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        return $notifications->contains(function ($notification) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (data_get($notification->data, $key) != $value) {
                    return false;
                }
            }

            return true;
        });
    }
}
