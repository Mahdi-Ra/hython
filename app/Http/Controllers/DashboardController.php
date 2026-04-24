<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Letter;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $visibleLetters = Letter::query();
        $this->scopeLettersForUser($visibleLetters, $user);

        $totalLetters = (clone $visibleLetters)->count();
        $myLetters = Letter::where('user_id', $user->id)->count();
        $pendingLetters = (clone $visibleLetters)->where('status', Letter::STATUS_PENDING)->count();
        $completedLetters = (clone $visibleLetters)->where('status', Letter::STATUS_COMPLETED)->count();
        $latestLetters = (clone $visibleLetters)->with('user')->latest()->take(5)->get();

        $assignedColumn = Schema::hasColumn('tasks', 'assigned_to_user_id')
            ? 'assigned_to_user_id'
            : (Schema::hasColumn('tasks', 'assigned_to') ? 'assigned_to' : null);
        $createdByColumn = Schema::hasColumn('tasks', 'created_by_user_id') ? 'created_by_user_id' : null;

        $visibleTasks = Task::query();
        $this->scopeTasksForUser($visibleTasks, $user);
        $latestTasks = $visibleTasks->latest()->take(5)->get();

        $userIds = collect();
        if ($assignedColumn) {
            $userIds = $userIds->merge($latestTasks->pluck($assignedColumn));
        }
        if ($createdByColumn) {
            $userIds = $userIds->merge($latestTasks->pluck($createdByColumn));
        }
        $userNamesById = User::query()
            ->whereIn('id', $userIds->filter()->unique()->values())
            ->pluck('name', 'id');

        $latestTasks->each(function (Task $task) use ($assignedColumn, $createdByColumn, $userNamesById) {
            $assignedUserId = $assignedColumn ? $task->getAttribute($assignedColumn) : null;
            $createdByUserId = $createdByColumn ? $task->getAttribute($createdByColumn) : null;
            $task->dashboard_assignee_name = $userNamesById[$assignedUserId]
                ?? $userNamesById[$createdByUserId]
                ?? null;
        });

        $latestMessages = ChatMessage::query()
            ->with('sender:id,name')
            ->where('receiver_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalLetters',
            'myLetters',
            'pendingLetters',
            'completedLetters',
            'latestLetters',
            'latestTasks',
            'latestMessages'
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
