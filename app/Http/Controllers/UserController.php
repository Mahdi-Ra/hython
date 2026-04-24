<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $users = User::with('department')->latest()->paginate(15);

        return view('employees.index', compact('users'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:' . implode(',', User::ROLES),
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'permissions' => [],
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'کارمند با موفقیت ایجاد شد.');
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('employees.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:' . implode(',', User::ROLES),
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'کارمند بروزرسانی شد.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->hasPermission(User::PERMISSION_EMPLOYEES_MANAGE), 403);

        $user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'کارمند حذف شد.');
    }
}
