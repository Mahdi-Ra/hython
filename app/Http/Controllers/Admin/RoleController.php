<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_PERMISSIONS_MANAGE), 403);

        $users = User::query()
            ->with('department')
            ->orderBy('name')
            ->paginate(20);

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        $permissionGroups = User::permissionGroups();

        return view('admin.roles.index', compact('users', 'departments', 'permissionGroups'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_PERMISSIONS_MANAGE), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:' . implode(',', User::ROLES)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::PERMISSION_LABELS))],
        ]);

        $user->update([
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->route('management.roles.index')->with('success', 'سطح دسترسی به‌روزرسانی شد.');
    }
}
