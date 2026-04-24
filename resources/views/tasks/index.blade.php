@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">مدیریت وظایف</h1>
            <p class="mt-1 text-sm text-gray-500">لیست وظایف و بروزرسانی وضعیت</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tasks.index', request()->query()) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                نمای لیستی
            </a>
            <a href="{{ route('tasks.kanban', request()->query()) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                نمای کانبان
            </a>
            @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_TASKS_CREATE))
                <a href="{{ route('tasks.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">ایجاد وظیفه</a>
            @endif
        </div>
    </div>

    <form method="GET" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7" data-jalali-filter-form>
        <input name="q" value="{{ request('q') }}" placeholder="جستجو در عنوان"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">همه وضعیت‌ها</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Task::statusLabel($status) }}</option>
            @endforeach
        </select>
        <select name="priority" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">همه اولویت‌ها</option>
            @foreach($priorities as $priority)
                <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ \App\Models\Task::priorityLabel($priority) }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">همه مسئولین</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected(request('assigned_to') == $employee->id)>{{ $employee->name }}</option>
            @endforeach
        </select>
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="to" value="{{ request('to') }}">
        <input type="text" data-jalali-visible="from" placeholder="از تاریخ ۱۴۰۵/۰۱/۰۱" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <input type="text" data-jalali-visible="to" placeholder="تا تاریخ ۱۴۰۵/۰۱/۳۰" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select name="overdue" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">همه مهلت‌ها</option>
            <option value="1" @selected(request('overdue') === '1')>معوق</option>
        </select>
        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">اعمال فیلتر</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-right text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3">عنوان</th>
                    <th class="px-4 py-3">مسئول</th>
                    <th class="px-4 py-3">اولویت</th>
                    <th class="px-4 py-3">وضعیت</th>
                    <th class="px-4 py-3">مهلت</th>
                    <th class="px-4 py-3">عملیات</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tasks as $task)
                    <tr>
                        <td class="px-4 py-3 text-gray-800">
                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline">
                                {{ $task->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $task->assignedTo->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ \App\Models\Task::priorityLabel($task->priority) }}
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('tasks.update', $task) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                                    <option value="pending" @selected($task->status === 'pending')>{{ \App\Models\Task::statusLabel('pending') }}</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>{{ \App\Models\Task::statusLabel('in_progress') }}</option>
                                    <option value="done" @selected($task->status === 'done')>{{ \App\Models\Task::statusLabel('done') }}</option>
                                </select>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ \App\Support\JalaliDate::format($task->due_date) }}</td>
                        <td class="px-4 py-3">
                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700">ذخیره</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">وظیفه‌ای وجود ندارد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tasks->hasPages())
        <div class="mt-4">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
