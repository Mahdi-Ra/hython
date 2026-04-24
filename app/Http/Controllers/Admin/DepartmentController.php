<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE), 403);

        $departments = Department::query()
            ->with('parent')
            ->orderBy('name')
            ->paginate(15);

        $parents = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.departments.index', compact('departments', 'parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:departments,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        Department::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('management.departments.index')->with('success', 'دپارتمان ایجاد شد.');
    }

    public function edit(Department $department): View
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE), 403);

        $parents = Department::query()
            ->where('id', '!=', $department->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.departments.edit', compact('department', 'parents'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:departments,slug,' . $department->id],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $department->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('management.departments.index')->with('success', 'دپارتمان به‌روزرسانی شد.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE), 403);

        $department->delete();

        return redirect()->route('management.departments.index')->with('success', 'دپارتمان حذف شد.');
    }
}
