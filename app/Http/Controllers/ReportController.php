<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->hasPermission(User::PERMISSION_REPORTS_VIEW), 403);

        $from = request('from');
        $to = request('to');
        $departmentId = request('department_id');

        $letterQuery = Letter::query();
        $taskQuery = Task::query();
        $this->scopeLettersForUser($letterQuery, $user);
        $this->scopeTasksForUser($taskQuery, $user);

        if ($from) {
            $letterQuery->whereDate('created_at', '>=', $from);
            $taskQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $letterQuery->whereDate('created_at', '<=', $to);
            $taskQuery->whereDate('created_at', '<=', $to);
        }
        if ($departmentId) {
            $letterQuery->where('department_id', $departmentId);
        }

        $lettersByStatus = (clone $letterQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $lettersByPriority = (clone $letterQuery)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $tasksByStatus = (clone $taskQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tasksByPriority = (clone $taskQuery)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $lettersByDepartment = Department::query()
            ->withCount(['letters' => function ($q) use ($from, $to, $user) {
                $this->scopeLettersForUser($q, $user);
                if ($from) {
                    $q->whereDate('created_at', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('created_at', '<=', $to);
                }
            }])
            ->orderByDesc('letters_count')
            ->limit(8)
            ->get();

        $topTaskOwners = (clone $taskQuery)
            ->selectRaw('assigned_to_user_id, COUNT(*) as total')
            ->whereNotNull('assigned_to_user_id')
            ->groupBy('assigned_to_user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('assignedTo:id,name')
            ->get();

        $topTaskCompleters = (clone $taskQuery)
            ->selectRaw('assigned_to_user_id, COUNT(*) as total')
            ->whereNotNull('assigned_to_user_id')
            ->where('status', 'done')
            ->groupBy('assigned_to_user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('assignedTo:id,name')
            ->get();

        $topLetterCreators = (clone $letterQuery)
            ->selectRaw('user_id, COUNT(*) as total')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('user:id,name')
            ->get();

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.index', compact(
            'lettersByStatus',
            'lettersByPriority',
            'tasksByStatus',
            'tasksByPriority',
            'lettersByDepartment',
            'topTaskOwners',
            'topTaskCompleters',
            'topLetterCreators',
            'departments',
            'from',
            'to',
            'departmentId'
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
