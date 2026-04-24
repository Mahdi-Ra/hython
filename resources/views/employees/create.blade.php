@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl p-4 md:p-6" dir="rtl">
    <div class="rounded-xl bg-white p-6 shadow">
        <h2 class="mb-6 text-xl font-bold text-gray-800">افزودن کارمند</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('employees.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">نام</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">ایمیل</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">رمز عبور</label>
                <input id="password" type="password" name="password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">تکرار رمز عبور</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label for="role" class="mb-1 block text-sm font-medium text-gray-700">نقش</label>
                <select id="role" name="role"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">انتخاب کنید</option>
                    <option value="admin" @selected(old('role') === 'admin')>ادمین</option>
                    <option value="manager" @selected(old('role') === 'manager')>مدیر</option>
                    <option value="employee" @selected(old('role', 'employee') === 'employee')>کارمند</option>
                </select>
            </div>

            <div>
                <label for="department_id" class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                <select id="department_id" name="department_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">بدون دپارتمان</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    انصراف
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    ثبت کارمند
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
