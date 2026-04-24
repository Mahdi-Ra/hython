@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Workload تیم</h1>
        <p class="mt-1 text-sm text-gray-500">ظرفیت افراد، فشار کاری و تراکم وظایف را یکجا ببینید</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($employees as $employee)
            @php
                $statusClasses = match($employee->workload_status) {
                    'overloaded' => 'bg-red-100 text-red-700',
                    'busy' => 'bg-amber-100 text-amber-700',
                    default => 'bg-emerald-100 text-emerald-700',
                };
                $barClasses = match($employee->workload_status) {
                    'overloaded' => 'bg-red-500',
                    'busy' => 'bg-amber-500',
                    default => 'bg-emerald-500',
                };
            @endphp
            <div class="rounded-2xl bg-white p-5 shadow">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">{{ $employee->name }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ $employee->department?->name ?? 'بدون دپارتمان' }}</div>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses }}">
                        {{ $employee->workload_status === 'overloaded' ? 'Overloaded' : ($employee->workload_status === 'busy' ? 'Busy' : 'Balanced') }}
                    </span>
                </div>

                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                        <span>امتیاز فشار کاری</span>
                        <span>{{ $employee->workload_score }} / 10</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100">
                        <div class="h-2.5 rounded-full {{ $barClasses }}" style="width: {{ $employee->workload_percent }}%"></div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-gray-500">وظایف باز</div>
                        <div class="mt-1 text-xl font-bold text-gray-800">{{ $employee->workload_open_tasks }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-gray-500">وظایف معوق</div>
                        <div class="mt-1 text-xl font-bold text-red-600">{{ $employee->workload_overdue_tasks }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-gray-500">وظایف فوری</div>
                        <div class="mt-1 text-xl font-bold text-amber-600">{{ $employee->workload_urgent_tasks }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-gray-500">ارجاع‌های باز</div>
                        <div class="mt-1 text-xl font-bold text-blue-600">{{ $employee->workload_pending_referrals }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
