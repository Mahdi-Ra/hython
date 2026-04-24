@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">گزارش فعالیت‌ها</h1>
            <p class="mt-1 text-sm text-gray-500">آخرین رویدادهای ثبت‌شده در سیستم</p>
        </div>
        <a href="{{ route('management.index') }}" class="text-sm text-blue-600 hover:underline">بازگشت</a>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-right text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3">کاربر</th>
                    <th class="px-4 py-3">مدل</th>
                    <th class="px-4 py-3">شناسه</th>
                    <th class="px-4 py-3">رویداد</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">زمان</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-gray-800">{{ $log->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ class_basename($log->subject_type) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->subject_id }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $log->event }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \App\Support\JalaliDate::format($log->created_at, true) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">موردی ثبت نشده است.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
