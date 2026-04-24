<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::latest()->paginate(10);
        return view('dashboard.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('dashboard.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        Employee::create($request->all());

        return redirect()->route('employees.index')
            ->with('success', 'کارمند با موفقیت اضافه شد');
    }

    public function edit(Employee $employee)
    {
        return view('dashboard.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $employee->update($request->all());

        return redirect()->route('employees.index')
            ->with('success', 'اطلاعات کارمند بروزرسانی شد');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with('success', 'کارمند حذف شد');
    }
}