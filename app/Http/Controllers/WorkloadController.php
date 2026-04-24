<?php

namespace App\Http\Controllers;

use App\Models\LetterReferral;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class WorkloadController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(User::PERMISSION_WORKLOAD_VIEW), 403);

        $employees = User::query()
            ->with('department:id,name')
            ->when(
                ! $user->hasPermission(User::PERMISSION_TASKS_VIEW_ALL) && $user->department_id,
                fn ($query) => $query->where('department_id', $user->department_id)
            )
            ->orderBy('name')
            ->get();

        $employees->each(function (User $employee) {
            $openTasks = Task::query()
                ->forUser($employee->id)
                ->where('status', '!=', Task::STATUS_COMPLETED)
                ->count();

            $overdueTasks = Task::query()
                ->forUser($employee->id)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->where('status', '!=', Task::STATUS_COMPLETED)
                ->count();

            $urgentTasks = Task::query()
                ->forUser($employee->id)
                ->whereIn('priority', [Task::PRIORITY_HIGH, Task::PRIORITY_URGENT])
                ->where('status', '!=', Task::STATUS_COMPLETED)
                ->count();

            $pendingReferrals = LetterReferral::query()
                ->where('to_user_id', $employee->id)
                ->whereIn('status', [LetterReferral::STATUS_PENDING, LetterReferral::STATUS_ACCEPTED])
                ->count();

            $capacityLimit = 10;
            $score = $openTasks + ($overdueTasks * 2) + ($urgentTasks * 2) + $pendingReferrals;

            $employee->workload_open_tasks = $openTasks;
            $employee->workload_overdue_tasks = $overdueTasks;
            $employee->workload_urgent_tasks = $urgentTasks;
            $employee->workload_pending_referrals = $pendingReferrals;
            $employee->workload_score = $score;
            $employee->workload_percent = (int) min(100, round(($score / $capacityLimit) * 100));
            $employee->workload_status = $score >= 12 ? 'overloaded' : ($score >= 7 ? 'busy' : 'balanced');
        });

        return view('workload.index', compact('employees'));
    }
}
