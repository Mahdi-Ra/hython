@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">کانبان وظایف</h1>
            <p class="mt-1 text-sm text-gray-500">مدیریت دیداری وظایف با جابه‌جایی بین ستون‌ها</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tasks.index', request()->query()) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                نمای لیستی
            </a>
            @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_TASKS_CREATE))
                <a href="{{ route('tasks.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">ایجاد وظیفه</a>
            @endif
        </div>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-7" data-jalali-filter-form>
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

    <div class="mb-4 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
        کارت‌هایی که اجازه مدیریت دارند با drag & drop بین ستون‌ها جابه‌جا می‌شوند. بعد از رها کردن، وضعیت همان لحظه ذخیره می‌شود.
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        @foreach(\App\Models\Task::STATUSES as $status)
            <section class="rounded-2xl bg-white p-4 shadow" data-kanban-column data-status="{{ $status }}">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ \App\Models\Task::statusLabel($status) }}</h3>
                        <p class="text-xs text-gray-500">{{ $groupedTasks[$status]->count() }} وظیفه</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">{{ \App\Models\Task::statusLabel($status) }}</span>
                </div>

                <div class="space-y-3 min-h-[14rem]" data-kanban-list>
                    @forelse($groupedTasks[$status] as $task)
                        <article
                            class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition {{ $task->can_manage ? 'cursor-move hover:border-indigo-300 hover:bg-indigo-50' : '' }}"
                            data-kanban-card
                            data-task-id="{{ $task->id }}"
                            data-status="{{ $task->status }}"
                            data-can-manage="{{ $task->can_manage ? '1' : '0' }}"
                            draggable="{{ $task->can_manage ? 'true' : 'false' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-gray-800 hover:text-indigo-700">
                                        {{ $task->title }}
                                    </a>
                                    <p class="mt-2 text-xs text-gray-500">{{ $task->assignedTo?->name ?? 'بدون مسئول' }}</p>
                                </div>
                                <span class="rounded-full px-2 py-1 text-xs {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($task->priority === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ \App\Models\Task::priorityLabel($task->priority) }}
                                </span>
                            </div>
                            @if(filled($task->description))
                                <p class="mt-3 text-sm leading-7 text-gray-600">{{ \Illuminate\Support\Str::limit($task->description, 110) }}</p>
                            @endif
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                <span>مهلت: {{ \App\Support\JalaliDate::format($task->due_date) }}</span>
                                @if(! $task->can_manage)
                                    <span>فقط مشاهده</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-400">
                            موردی در این ستون نیست.
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const taskBaseUrl = @json(url('/tasks'));
    const cards = document.querySelectorAll('[data-kanban-card]');
    const columns = document.querySelectorAll('[data-kanban-column]');
    let activeCard = null;

    cards.forEach((card) => {
        if (card.dataset.canManage !== '1') {
            return;
        }

        card.addEventListener('dragstart', () => {
            activeCard = card;
            card.classList.add('opacity-60');
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-60');
        });
    });

    columns.forEach((column) => {
        column.addEventListener('dragover', (event) => {
            if (!activeCard) {
                return;
            }

            event.preventDefault();
            column.classList.add('ring-2', 'ring-indigo-300');
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('ring-2', 'ring-indigo-300');
        });

        column.addEventListener('drop', async (event) => {
            event.preventDefault();
            column.classList.remove('ring-2', 'ring-indigo-300');

            if (!activeCard) {
                return;
            }

            const newStatus = column.dataset.status;
            const currentStatus = activeCard.dataset.status;

            if (newStatus === currentStatus) {
                return;
            }

            const body = new URLSearchParams({
                _method: 'PUT',
                status: newStatus,
            });

            try {
                const response = await fetch(`${taskBaseUrl}/${activeCard.dataset.taskId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    },
                    body,
                });

                if (!response.ok) {
                    throw new Error('status update failed');
                }

                window.location.reload();
            } catch (error) {
                window.location.reload();
            }
        });
    });
});
</script>
@endsection
