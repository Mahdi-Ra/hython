@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">داشبورد KPI</h1>
        <p class="mt-1 text-sm text-gray-500">شاخص‌های کلیدی عملکرد سازمان</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">کل نامه‌ها</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $totalLetters }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">نامه‌های باز</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $openLetters }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">نامه‌های معوق</div>
            <div class="mt-2 text-2xl font-bold text-red-600">{{ $overdueLetters }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">کل وظایف</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $totalTasks }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">وظایف باز</div>
            <div class="mt-2 text-2xl font-bold text-gray-800">{{ $openTasks }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">وظایف معوق</div>
            <div class="mt-2 text-2xl font-bold text-red-600">{{ $overdueTasks }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">نامه‌ها بر اساس دپارتمان</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                @forelse($lettersByDepartment as $department)
                    <li class="flex items-center justify-between">
                        <span>{{ $department->name }}</span>
                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                            {{ $department->letters_count }}
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">وظایف بر اساس دپارتمان</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                @forelse($tasksByDepartment as $department)
                    <li class="flex items-center justify-between">
                        <span>{{ $department->name }}</span>
                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                            {{ $department->tasks_count }}
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
