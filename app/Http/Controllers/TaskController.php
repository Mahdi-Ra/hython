<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Support\AutomationEngine;
use App\Support\CollaborationService;
use App\Support\RecordTimelineBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = $this->applyTaskFilters($this->baseScopedQuery($user));

        $tasks = $query->latest()->paginate(15)->withQueryString();

        $employees = User::query()->orderBy('name')->get(['id', 'name']);

        $statuses = ['pending', 'in_progress', 'done'];
        $priorities = ['low', 'normal', 'high', 'urgent'];

        return view('tasks.index', compact('tasks', 'employees', 'statuses', 'priorities'));
    }

    public function kanban(): View
    {
        $user = auth()->user();
        $tasks = $this->applyTaskFilters($this->baseScopedQuery($user))
            ->latest()
            ->get()
            ->each(function (Task $task) use ($user) {
                $task->setAttribute('can_manage', $this->canManageTask($task, $user));
            });

        $groupedTasks = collect(Task::STATUSES)->mapWithKeys(function (string $status) use ($tasks) {
            return [$status => $tasks->where('status', $status)->values()];
        });

        $employees = User::query()->orderBy('name')->get(['id', 'name']);
        $statuses = Task::STATUSES;
        $priorities = Task::PRIORITIES;

        return view('tasks.kanban', compact('groupedTasks', 'employees', 'statuses', 'priorities'));
    }

    public function create(): View
    {
        $this->authorizeTaskCreation();

        $user = auth()->user();
        $employees = User::query()->latest()->get(['id', 'name', 'department_id']);
        if ($user->isManager() && $user->department_id) {
            $employees = $employees->where('department_id', $user->department_id);
        }

        return view('tasks.create', compact('employees'));
    }

    public function show(Task $task): View
    {
        $user = auth()->user();
        abort_unless($this->canViewTask($task, $user), 403);

        $task->load([
            'assignedTo:id,name,department_id',
            'createdBy:id,name',
            'letter:id,uuid,subject,reference_number',
            'comments.user:id,name',
            'auditLogs.user:id,name',
            'followers.user:id,name,email',
        ]);

        $timeline = RecordTimelineBuilder::forTask($task);
        $canManageTask = $this->canManageTask($task, $user);
        $isFollowing = $task->isFollowedBy($user);

        return view('tasks.show', compact('task', 'timeline', 'canManageTask', 'isFollowing'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTaskCreation();
        $user = auth()->user();

        $assignedColumn = Schema::hasColumn('tasks', 'assigned_to_user_id') ? 'assigned_to_user_id' : 'assigned_to';

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            $assignedColumn => (int) $validated['assigned_to'],
        ];

        if ($user->hasPermission(User::PERMISSION_TASKS_MANAGE_DEPARTMENT) && $user->department_id && ! $user->hasPermission(User::PERMISSION_TASKS_MANAGE_ALL)) {
            $assignedUser = User::query()->find($validated['assigned_to']);
            if (! $assignedUser || $assignedUser->department_id !== $user->department_id) {
                abort(403);
            }
        }

        if (Schema::hasColumn('tasks', 'created_by_user_id')) {
            $payload['created_by_user_id'] = auth()->id();
        }

        if (Schema::hasColumn('tasks', 'status')) {
            $payload['status'] = Task::STATUS_PENDING;
        }

        if (Schema::hasColumn('tasks', 'priority')) {
            $payload['priority'] = $validated['priority'] ?? Task::PRIORITY_NORMAL;
        }
        if (Schema::hasColumn('tasks', 'due_date')) {
            $payload['due_date'] = $validated['due_date'] ?? null;
        }

        $task = Task::query()->create($payload);

        app(AutomationEngine::class)->trigger('task_created', [
            'task' => $task,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'department_id' => $task->assignedTo?->department_id,
            'priority' => $task->priority,
            'actor_id' => $user->id,
        ]);

        return redirect()->route('tasks.index')->with('success', 'وظیفه با موفقیت ثبت شد.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();
        if (! $this->canManageTask($task, $user)) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,done'],
        ]);

        $task->status = $validated['status'];
        $task->completed_at = $validated['status'] === Task::STATUS_COMPLETED ? now() : null;
        $task->save();

        if ($task->wasChanged('status')) {
            app(CollaborationService::class)->notifyFollowers(
                $task,
                'تغییر وضعیت وظیفه',
                sprintf(
                    '%s وضعیت وظیفه «%s» را به %s تغییر داد.',
                    $user->name,
                    $task->title,
                    Task::statusLabel($task->status)
                ),
                [$user->id]
            );
        }

        return redirect()->back()->with('success', 'وضعیت وظیفه بروزرسانی شد.');
    }

    public function storeComment(Request $request, Task $task): RedirectResponse
    {
        abort_unless($this->canViewTask($task, auth()->user()), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        app(CollaborationService::class)->handleComment($comment->load('user', 'commentable'));

        return redirect()->route('tasks.show', $task)->with('success', 'یادداشت وظیفه ثبت شد.');
    }

    public function follow(Task $task): RedirectResponse
    {
        abort_unless($this->canViewTask($task, auth()->user()), 403);

        $task->followers()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('tasks.show', $task)->with('success', 'وظیفه به موارد دنبال‌شده شما اضافه شد.');
    }

    public function unfollow(Task $task): RedirectResponse
    {
        abort_unless($this->canViewTask($task, auth()->user()), 403);

        $task->followers()->where('user_id', auth()->id())->delete();

        return redirect()->route('tasks.show', $task)->with('success', 'وظیفه از موارد دنبال‌شده حذف شد.');
    }

    private function baseScopedQuery(User $user): Builder
    {
        $query = Task::query()->with('assignedTo');

        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_ALL)) {
            return $query;
        }

        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_DEPARTMENT) && $user->department_id) {
            $departmentUserIds = User::query()
                ->where('department_id', $user->department_id)
                ->pluck('id');

            return $query->where(function (Builder $sub) use ($departmentUserIds) {
                $sub->whereIn('assigned_to_user_id', $departmentUserIds)
                    ->orWhereIn('assigned_to', $departmentUserIds);
            });
        }

        return $query->forUser($user->id);
    }

    private function applyTaskFilters(Builder $query): Builder
    {
        $query->when(request('status'), function (Builder $q, $status) {
            $q->where('status', $status);
        });

        $query->when(request('priority'), function (Builder $q, $priority) {
            $q->where('priority', $priority);
        });

        $query->when(request('assigned_to'), function (Builder $q, $assignedTo) {
            $q->where(function (Builder $sub) use ($assignedTo) {
                $sub->where('assigned_to_user_id', $assignedTo)
                    ->orWhere('assigned_to', $assignedTo);
            });
        });

        $query->when(request('from'), function (Builder $q, $from) {
            $q->whereDate('created_at', '>=', $from);
        });

        $query->when(request('to'), function (Builder $q, $to) {
            $q->whereDate('created_at', '<=', $to);
        });

        $query->when(request('overdue'), function (Builder $q) {
            $q->whereNotNull('due_date')
                ->whereDate('due_date', '<', now())
                ->where('status', '!=', Task::STATUS_COMPLETED);
        });

        $query->when(request('q'), function (Builder $q, $search) {
            $q->where('title', 'like', '%' . $search . '%');
        });

        return $query;
    }

    private function authorizeTaskCreation(): void
    {
        if (! auth()->user()->hasPermission(User::PERMISSION_TASKS_CREATE)) {
            abort(403);
        }
    }

    private function canViewTask(Task $task, User $user): bool
    {
        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_ALL)) {
            return true;
        }

        if ($user->hasPermission(User::PERMISSION_TASKS_VIEW_DEPARTMENT) && $user->department_id) {
            return (int) $task->assignedTo?->department_id === (int) $user->department_id;
        }

        return (int) $task->assignedTo?->id === (int) $user->id
            || (int) $task->createdBy?->id === (int) $user->id;
    }

    private function canManageTask(Task $task, User $user): bool
    {
        if ($user->hasPermission(User::PERMISSION_TASKS_MANAGE_ALL)) {
            return true;
        }

        if ($user->hasPermission(User::PERMISSION_TASKS_MANAGE_DEPARTMENT) && $user->department_id) {
            return (int) $task->assignedTo?->department_id === (int) $user->department_id;
        }

        return (int) $task->assignedTo?->id === (int) $user->id;
    }
}
