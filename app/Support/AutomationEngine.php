<?php

namespace App\Support;

use App\Models\AutomationRule;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AutomationTriggeredNotification;

class AutomationEngine
{
    public function trigger(string $event, array $context = []): int
    {
        $rules = AutomationRule::query()
            ->active()
            ->where('event', $event)
            ->get();

        $executed = 0;
        foreach ($rules as $rule) {
            if (! $this->matchesRule($rule, $context)) {
                continue;
            }

            if ($this->executeRule($rule, $context)) {
                $executed++;
            }
        }

        return $executed;
    }

    private function matchesRule(AutomationRule $rule, array $context): bool
    {
        if ($rule->department_id && (int) ($context['department_id'] ?? 0) !== (int) $rule->department_id) {
            return false;
        }

        if ($rule->priority && ($context['priority'] ?? null) !== $rule->priority) {
            return false;
        }

        return true;
    }

    private function executeRule(AutomationRule $rule, array $context): bool
    {
        return match ($rule->action) {
            AutomationRule::ACTION_NOTIFY_USER => $this->notifyUser($rule, $context),
            AutomationRule::ACTION_NOTIFY_ROLE => $this->notifyRole($rule, $context),
            AutomationRule::ACTION_CREATE_TASK => $this->createTask($rule, $context),
            default => false,
        };
    }

    private function notifyUser(AutomationRule $rule, array $context): bool
    {
        if (! $rule->target_user_id) {
            return false;
        }

        $user = User::query()->find($rule->target_user_id);
        if (! $user) {
            return false;
        }

        $user->notify(new AutomationTriggeredNotification(
            title: 'اجرای اتوماسیون: ' . $rule->name,
            body: $this->buildAutomationBody($context),
            actionUrl: $this->resolveActionUrl($context),
            actionLabel: 'مشاهده'
        ));

        return true;
    }

    private function notifyRole(AutomationRule $rule, array $context): bool
    {
        if (! $rule->target_role) {
            return false;
        }

        $query = User::query()->where('role', $rule->target_role);
        if (! empty($context['department_id'])) {
            $query->where('department_id', $context['department_id']);
        }

        $users = $query->get();
        if ($users->isEmpty()) {
            return false;
        }

        foreach ($users as $user) {
            $user->notify(new AutomationTriggeredNotification(
                title: 'اجرای اتوماسیون: ' . $rule->name,
                body: $this->buildAutomationBody($context),
                actionUrl: $this->resolveActionUrl($context),
                actionLabel: 'مشاهده'
            ));
        }

        return true;
    }

    private function createTask(AutomationRule $rule, array $context): bool
    {
        $assignee = $this->resolveAssignee($rule, $context);
        if (! $assignee) {
            return false;
        }

        $title = $this->replaceTokens($rule->task_title_template ?: 'پیگیری مورد جدید', $context);
        $description = $this->replaceTokens($rule->task_description_template ?: '', $context);
        $existingTask = Task::query()
            ->where('title', $title)
            ->where('status', '!=', Task::STATUS_COMPLETED)
            ->whereDate('created_at', '>=', now()->subDays(3)->toDateString())
            ->where(function ($query) use ($assignee) {
                $query->where('assigned_to_user_id', $assignee->id)
                    ->orWhere('assigned_to', $assignee->id);
            })
            ->when(! empty($context['letter_id']), fn ($query) => $query->where('letter_id', $context['letter_id']))
            ->first();

        if ($existingTask) {
            return false;
        }

        $task = Task::query()->create([
            'letter_id' => $context['letter_id'] ?? null,
            'assigned_to' => $assignee->id,
            'assigned_to_user_id' => $assignee->id,
            'created_by_user_id' => $context['actor_id'] ?? null,
            'title' => $title,
            'description' => $description,
            'status' => Task::STATUS_PENDING,
            'priority' => $rule->task_priority ?: Task::PRIORITY_NORMAL,
            'due_date' => now()->addDays(max(1, $rule->due_in_days ?? 1))->toDateString(),
        ]);

        return (bool) $task;
    }

    private function resolveAssignee(AutomationRule $rule, array $context): ?User
    {
        if ($rule->target_user_id) {
            return User::query()->find($rule->target_user_id);
        }

        if (! $rule->target_role) {
            return null;
        }

        $query = User::query()->where('role', $rule->target_role);
        if (! empty($context['department_id'])) {
            $query->where('department_id', $context['department_id']);
        }

        return $query->orderBy('id')->first();
    }

    private function buildAutomationBody(array $context): string
    {
        if (! empty($context['letter_subject'])) {
            return 'برای نامه «' . $context['letter_subject'] . '» یک اتوماسیون اجرا شد.';
        }

        if (! empty($context['task_title'])) {
            return 'برای وظیفه «' . $context['task_title'] . '» یک اتوماسیون اجرا شد.';
        }

        return 'یک اتوماسیون سازمانی اجرا شد.';
    }

    private function resolveActionUrl(array $context): ?string
    {
        if (! empty($context['letter'])) {
            return route('letters.view', $context['letter']);
        }

        if (! empty($context['task'])) {
            return route('tasks.show', $context['task']);
        }

        return null;
    }

    private function replaceTokens(string $template, array $context): string
    {
        $replacements = [
            '{letter_subject}' => $context['letter_subject'] ?? '',
            '{letter_reference}' => $context['letter_reference'] ?? '',
            '{task_title}' => $context['task_title'] ?? '',
            '{department_name}' => $context['department_name'] ?? '',
        ];

        return strtr($template, $replacements);
    }
}
