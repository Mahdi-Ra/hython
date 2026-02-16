<?php

namespace App\Observers;

use App\Models\Task;
use App\Notifications\TaskAssignedNotification;

class TaskObserver
{
    public function created(Task $task): void
    {
        if ($task->assigned_to_user_id) {
            $task->load('letter');
            $task->assignedTo->notify(new TaskAssignedNotification($task));
        }
    }

    public function updated(Task $task): void
    {
        if (! $task->wasChanged('assigned_to_user_id')) {
            return;
        }
        $newAssigneeId = $task->assigned_to_user_id;
        if ($newAssigneeId) {
            $task->load('letter');
            $task->assignedTo->notify(new TaskAssignedNotification($task));
        }
    }
}
