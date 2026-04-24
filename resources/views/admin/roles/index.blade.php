@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">سطح دسترسی کاربران</h1>
            <p class="mt-1 text-sm text-gray-500">نقش، دپارتمان و permissionهای قابل‌تنظیم هر کاربر را مدیریت کنید.</p>
        </div>
        <a href="{{ route('management.index') }}" class="text-sm text-blue-600 hover:underline">بازگشت</a>
    </div>

    <div class="space-y-4">
        @foreach($users as $user)
            <form action="{{ route('management.roles.update', $user) }}" method="POST" class="rounded-2xl bg-white p-5 shadow">
                @csrf
                @method('PUT')

                <div class="grid gap-4 lg:grid-cols-4">
                    <div class="lg:col-span-1">
                        <div class="text-lg font-semibold text-gray-800">{{ $user->name }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ $user->email }}</div>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">نقش</label>
                                <select name="role" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="admin" @selected($user->role === 'admin')>ادمین</option>
                                    <option value="manager" @selected($user->role === 'manager')>مدیر</option>
                                    <option value="employee" @selected($user->role === 'employee')>کارمند</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                                <select name="department_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">بدون دپارتمان</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($user->department_id == $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-800">مجوزهای اضافی</h2>
                            <span class="text-xs text-gray-500">مجوزهای پیش‌فرض نقش همچنان فعال می‌مانند؛ این‌ها مجوزهای تکمیلی‌اند.</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($permissionGroups as $groupTitle => $permissions)
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <div class="mb-3 text-sm font-semibold text-gray-800">{{ $groupTitle }}</div>
                                    <div class="space-y-2">
                                        @foreach($permissions as $permission)
                                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                                       @checked(in_array($permission, $user->effectivePermissions(), true))
                                                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ \App\Models\User::PERMISSION_LABELS[$permission] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        ذخیره دسترسی‌ها
                    </button>
                </div>
            </form>
        @endforeach
    </div>

    @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
