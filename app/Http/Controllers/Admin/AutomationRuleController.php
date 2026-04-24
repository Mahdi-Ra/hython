<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\Department;
use App\Models\Letter;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationRuleController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_AUTOMATIONS_MANAGE), 403);

        $rules = AutomationRule::query()
            ->with(['department:id,name', 'targetUser:id,name'])
            ->latest()
            ->get();

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->orderBy('name')->get(['id', 'name', 'role']);

        return view('admin.automations.index', compact('rules', 'departments', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_AUTOMATIONS_MANAGE), 403);

        $validated = $this->validateRule($request);
        AutomationRule::query()->create($validated);

        return redirect()->route('management.automations.index')->with('success', 'اتوماسیون ایجاد شد.');
    }

    public function update(Request $request, AutomationRule $automation): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_AUTOMATIONS_MANAGE), 403);

        $validated = $this->validateRule($request);
        $automation->update($validated);

        return redirect()->route('management.automations.index')->with('success', 'اتوماسیون بروزرسانی شد.');
    }

    public function destroy(AutomationRule $automation): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_AUTOMATIONS_MANAGE), 403);

        $automation->delete();

        return redirect()->route('management.automations.index')->with('success', 'اتوماسیون حذف شد.');
    }

    private function validateRule(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'event' => ['required', 'in:' . implode(',', AutomationRule::EVENTS)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'priority' => ['nullable', 'in:' . implode(',', array_merge(Letter::PRIORITIES, Task::PRIORITIES))],
            'action' => ['required', 'in:' . implode(',', AutomationRule::ACTIONS)],
            'target_role' => ['nullable', 'in:' . implode(',', User::ROLES)],
            'target_user_id' => ['nullable', 'exists:users,id'],
            'task_title_template' => ['nullable', 'string', 'max:255'],
            'task_description_template' => ['nullable', 'string'],
            'task_priority' => ['nullable', 'in:' . implode(',', Task::PRIORITIES)],
            'due_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_active' => (bool) $request->boolean('is_active'),
        ];
    }
}
