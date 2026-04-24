@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl p-4 md:p-6" dir="rtl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">مدیریت سیستم</h1>
        <p class="mt-1 text-sm text-gray-500">تنظیمات اصلی سازمان، ساختار واحدها و کنترل دسترسی‌ها</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_DEPARTMENTS_MANAGE))
            <a href="{{ route('management.departments.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow">
                <div class="text-lg font-semibold text-gray-800">دپارتمان‌ها</div>
                <div class="mt-1 text-sm text-gray-500">تعریف و مدیریت ساختار واحدها</div>
            </a>
        @endif

        @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_PERMISSIONS_MANAGE))
            <a href="{{ route('management.roles.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow">
                <div class="text-lg font-semibold text-gray-800">سطح دسترسی</div>
                <div class="mt-1 text-sm text-gray-500">نقش‌ها و permissionهای تکمیلی کاربران</div>
            </a>
        @endif

        @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_AUDIT_VIEW))
            <a href="{{ route('management.audit.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow">
                <div class="text-lg font-semibold text-gray-800">گزارش فعالیت‌ها</div>
                <div class="mt-1 text-sm text-gray-500">ثبت رویدادهای مهم سیستم</div>
            </a>
        @endif
        @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_AUTOMATIONS_MANAGE))
            <a href="{{ route('management.automations.index') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow">
                <div class="text-lg font-semibold text-gray-800">اتوماسیون‌ها</div>
                <div class="mt-1 text-sm text-gray-500">قوانین خودکار برای ثبت نامه، وظیفه و پیگیری</div>
            </a>
        @endif
    </div>
</div>
@endsection
