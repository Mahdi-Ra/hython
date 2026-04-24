<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class KpiController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(User::PERMISSION_KPIS_VIEW), 403);

        $letterQuery = Letter::query();
        $taskQuery = Task::query();
        $this->scopeLettersForUser($letterQuery, $user);
        $this->scopeTasksForUser($taskQuery, $user);

        $totalLetters = (clone $letterQuery)->count();
        $openLetters = (clone $letterQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $overdueLetters = (clone $letterQuery)->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'archived'])
            ->count();

        $totalTasks = (clone $taskQuery)->count();
        $openTasks = (clone $taskQuery)->whereIn('status', ['pending', 'in_progress'])->count();
        $overdueTasks = (clone $taskQuery)->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->where('status', '!=', 'done')
            ->count();

        $lettersByDepartment = Department::query()
            ->withCount(['letters' => function ($query) use ($user) {
                $this->scopeLettersForUser($query, $user);
            }])
            ->orderByDesc('letters_count')
            ->limit(6)
            ->get();

        $tasksByDepartment = Department::query()
            ->withCount(['users as tasks_count' => function ($q) use ($user) {
                if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_ALL)) {
                    $q->whereHas('tasksAssigned');
                } elseif ($user->hasPermission(User::PERMISSION_TASKS_VIEW_DEPARTMENT) && $user->department_id) {
                    $q->where('department_id', $user->department_id)->whereHas('tasksAssigned');
                } else {
                    $q->where('id', $user->id)->whereHas('tasksAssigned');
                }
            }])
            ->orderByDesc('tasks_count')
            ->limit(6)
            ->get();

        return view('kpis.index', compact(
            'totalLetters',
            'openLetters',
            'overdueLetters',
            'totalTasks',
            'openTasks',
            'overdueTasks',
            'lettersByDepartment',
            'tasksByDepartment'
        ));
    }

    private function scopeLettersForUser(Builder $query, User $user): void
    {
        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_ALL)) {
            return;
        }

        if ($user->hasPermission(User::PERMISSION_LETTERS_VIEW_DEPARTMENT) && $user->department_id) {
            $query->where('department_id', $user->department_id);

            return;
        }

        $query->where(function (Builder $sub) use ($user) {
            $sub->where('user_id', $user->id)
                ->orWhereHas('referrals', function (Builder $referrals) use ($user) {
                    $referrals->where('to_user_id', $user->id)
                        ->orWhere('from_user_id', $user->id)
                        ->orWhere('assigned_by_user_id', $user->id);
                });
        });
    }

    private function scopeTasksForUser(Builder $query, User $user): void
    {
        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_ALL)) {
            return;
        }

        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_DEPARTMENT) && $user->department_id) {
            $departmentUserIds = User::query()
                ->where('department_id', $user->department_id)
                ->pluck('id');

            $query->where(function (Builder $sub) use ($departmentUserIds) {
                $sub->whereIn('assigned_to_user_id', $departmentUserIds)
                    ->orWhereIn('assigned_to', $departmentUserIds);
            });

            return;
        }

        $query->forUser($user->id);
    }
}
