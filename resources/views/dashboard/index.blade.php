@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-3">
            <div class="sticky top-6 rounded-xl bg-white p-4 shadow">
                <h2 class="mb-4 text-lg font-bold text-gray-800">منوی داشبورد</h2>
                <nav class="space-y-2">
                    <a href="{{ route('my-work.index') }}" class="block rounded-lg bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100">
                        کارتابل من
                    </a>
                    <a href="{{ route('letters.index') }}" class="block rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200">
                        نامه ها
                    </a>
                    <a href="{{ route('tasks.index') }}" class="block rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200">
                        وظایف
                    </a>
                    @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_WORKLOAD_VIEW))
                        <a href="{{ route('workload.index') }}" class="block rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200">
                            Workload
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_EMPLOYEES_MANAGE))
                        <a href="{{ route('employees.index') }}" class="block rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200">
                            کارمندان
                        </a>
                    @endif
                    <a href="{{ route('messages.index') }}" class="block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        ارسال پیام
                    </a>
                </nav>
            </div>
        </aside>

        <section class="space-y-6 lg:col-span-9">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-white p-4 text-center shadow">
                    <p class="mb-2 text-sm text-gray-500">کل نامه‌ها</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalLetters ?? 0 }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 text-center shadow">
                    <p class="mb-2 text-sm text-gray-500">نامه‌های من</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $myLetters ?? 0 }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 text-center shadow">
                    <p class="mb-2 text-sm text-gray-500">در انتظار</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $pendingLetters ?? 0 }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 text-center shadow">
                    <p class="mb-2 text-sm text-gray-500">تکمیل شده</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $completedLetters ?? 0 }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow">
                <div class="border-b px-4 py-3 text-base font-bold text-gray-800">آخرین نامه‌ها</div>
                <div class="overflow-x-auto p-4">
                    <table class="min-w-full table-auto text-right text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-600">
                                <th class="px-3 py-2">موضوع</th>
                                <th class="px-3 py-2">فرستنده</th>
                                <th class="px-3 py-2">وضعیت</th>
                                <th class="px-3 py-2">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            @forelse($latestLetters as $letter)
                                <tr class="border-b last:border-0">
                                    <td class="px-3 py-2">
                                        <a href="{{ route('letters.view', $letter) }}" class="text-blue-600 hover:underline">
                                            {{ $letter->subject }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2">{{ $letter->user->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ \App\Models\Letter::statusLabel($letter->status) }}
                                    </td>
                                    <td class="px-3 py-2">{{ \App\Support\JalaliDate::format($letter->created_at, false, '-') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">نامه‌ای موجود نیست.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="latest-tasks" class="rounded-xl bg-white shadow">
                <div class="border-b px-4 py-3 text-base font-bold text-gray-800">آخرین وظایف</div>
                <div class="overflow-x-auto p-4">
                    <table class="min-w-full table-auto text-right text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-600">
                                <th class="px-3 py-2">عنوان</th>
                                <th class="px-3 py-2">مسئول</th>
                                <th class="px-3 py-2">وضعیت</th>
                                <th class="px-3 py-2">تاریخ ثبت</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            @forelse($latestTasks as $task)
                                <tr class="border-b last:border-0">
                                    <td class="px-3 py-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:underline">
                                            {{ $task->title }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-2">{{ $task->dashboard_assignee_name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ \App\Models\Task::statusLabel($task->status) }}
                                    </td>
                                    <td class="px-3 py-2">{{ \App\Support\JalaliDate::format($task->created_at, true, '-') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">وظیفه‌ای موجود نیست.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="latest-messages" class="rounded-xl bg-white shadow">
                <div class="border-b px-4 py-3 text-base font-bold text-gray-800">آخرین پیام‌های دریافتی</div>
                <div class="p-4">
                    <div class="space-y-3">
                        @forelse($latestMessages as $message)
                            <div class="rounded-lg border border-gray-200 p-3">
                                <div class="mb-1 flex items-center justify-between gap-3">
                                    <p class="font-semibold text-gray-800">از {{ $message->sender->name ?? 'همکار' }}</p>
                                    @if(is_null($message->read_at))
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">جدید</span>
                                    @endif
                                </div>
                                @if(filled($message->message))
                                    <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</p>
                                @else
                                    <p class="text-sm text-gray-600">فایل ارسال شده: {{ $message->attachment_name ?? 'پیوست' }}</p>
                                @endif
                                <div class="mt-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                                    <span>{{ \App\Support\JalaliDate::format($message->created_at, true) }}</span>
                                    <a href="{{ route('messages.show', $message->sender_id) }}" class="text-blue-600 hover:underline">مشاهده گفتگو</a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-gray-500">پیامی دریافت نشده است.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
