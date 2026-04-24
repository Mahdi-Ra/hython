@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">گزارش‌ها</h1>
            <p class="mt-1 text-sm text-gray-500">نمای کلی وضعیت نامه‌ها و وظایف</p>
        </div>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-4" data-jalali-filter-form>
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <input type="text" data-jalali-visible="from" placeholder="از تاریخ ۱۴۰۵/۰۱/۰۱" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <input type="text" data-jalali-visible="to" placeholder="تا تاریخ ۱۴۰۵/۰۱/۳۰" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select name="department_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">همه دپارتمان‌ها</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected($departmentId == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">اعمال فیلتر</button>
    </form>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">نامه‌ها بر اساس وضعیت</h2>
            <ul class="space-y-1 text-sm text-gray-600">
                @forelse($lettersByStatus as $status => $count)
                    <li class="flex justify-between">
                        <span>{{ \App\Models\Letter::statusLabel($status) }}</span>
                        <span class="font-semibold text-gray-800">{{ $count }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">نامه‌ها بر اساس اولویت</h2>
            <ul class="space-y-1 text-sm text-gray-600">
                @forelse($lettersByPriority as $priority => $count)
                    <li class="flex justify-between">
                        <span>{{ \App\Models\Letter::priorityLabel($priority) }}</span>
                        <span class="font-semibold text-gray-800">{{ $count }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">وظایف بر اساس وضعیت</h2>
            <ul class="space-y-1 text-sm text-gray-600">
                @forelse($tasksByStatus as $status => $count)
                    <li class="flex justify-between">
                        <span>{{ \App\Models\Task::statusLabel($status) }}</span>
                        <span class="font-semibold text-gray-800">{{ $count }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">وظایف بر اساس اولویت</h2>
            <ul class="space-y-1 text-sm text-gray-600">
                @forelse($tasksByPriority as $priority => $count)
                    <li class="flex justify-between">
                        <span>{{ \App\Models\Task::priorityLabel($priority) }}</span>
                        <span class="font-semibold text-gray-800">{{ $count }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow md:col-span-2">
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
            <h2 class="mb-2 text-base font-semibold text-gray-800">عملکرد کارمندان (وظایف)</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                @forelse($topTaskOwners as $item)
                    <li class="flex items-center justify-between">
                        <span>{{ $item->assignedTo->name ?? '—' }}</span>
                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                            {{ $item->total }}
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">عملکرد کارمندان (وظایف انجام‌شده)</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                @forelse($topTaskCompleters as $item)
                    <li class="flex items-center justify-between">
                        <span>{{ $item->assignedTo->name ?? '—' }}</span>
                        <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">
                            {{ $item->total }}
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">داده‌ای وجود ندارد.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
            <h2 class="mb-2 text-base font-semibold text-gray-800">عملکرد کارمندان (نامه‌ها)</h2>
            <ul class="space-y-2 text-sm text-gray-600">
                @forelse($topLetterCreators as $item)
                    <li class="flex items-center justify-between">
                        <span>{{ $item->user->name ?? '—' }}</span>
                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                            {{ $item->total }}
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
