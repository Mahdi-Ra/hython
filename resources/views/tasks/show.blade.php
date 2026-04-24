@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl p-4 md:p-6" dir="rtl">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
                        <p class="mt-1 text-sm text-gray-500">جزئیات، تاریخچه و پیگیری وظیفه</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        بازگشت به لیست
                    </a>
                </div>

                <div class="grid gap-4 rounded-xl bg-gray-50 p-4 text-sm text-gray-700 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <span class="text-gray-500">مسئول:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ $task->assignedTo?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">ایجادکننده:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ $task->createdBy?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">اولویت:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ \App\Models\Task::priorityLabel($task->priority) }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">وضعیت:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ \App\Models\Task::statusLabel($task->status) }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">مهلت:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ \App\Support\JalaliDate::format($task->due_date) }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">تاریخ ایجاد:</span>
                        <div class="mt-1 font-medium text-gray-800">{{ \App\Support\JalaliDate::format($task->created_at, true) }}</div>
                    </div>
                </div>

                @if(filled($task->description))
                    <div class="mt-6">
                        <h3 class="mb-2 text-base font-semibold text-gray-800">توضیحات</h3>
                        <div class="rounded-xl border border-gray-200 p-4 text-sm leading-8 text-gray-700 whitespace-pre-line">
                            {{ $task->description }}
                        </div>
                    </div>
                @endif

                @if($task->letter)
                    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4">
                        <div class="text-sm font-semibold text-blue-900">نامه مرتبط</div>
                        <div class="mt-1 text-sm text-blue-800">
                            {{ $task->letter->reference_number ?? '—' }} - {{ $task->letter->subject ?? '—' }}
                        </div>
                        <a href="{{ route('letters.view', $task->letter) }}" class="mt-2 inline-block text-sm text-blue-700 hover:underline">
                            مشاهده نامه
                        </a>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">تایم‌لاین وظیفه</h3>
                    <span class="text-xs text-gray-500">{{ $timeline->count() }} رویداد</span>
                </div>

                <div class="space-y-4">
                    @foreach($timeline as $item)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-semibold text-gray-800">{{ $item['title'] }}</div>
                                <div class="text-xs text-gray-500">{{ \App\Support\JalaliDate::format($item['at'], true) }}</div>
                            </div>
                            <p class="mt-2 text-sm text-gray-700">{{ $item['description'] }}</p>
                            @if(filled($item['meta'] ?? null))
                                <p class="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">{{ $item['meta'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl bg-white p-5 shadow">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">دنبال کردن وظیفه</h3>
                    <span class="text-xs text-gray-500">{{ $task->followers->count() }} دنبال‌کننده</span>
                </div>

                @if($isFollowing)
                    <form method="POST" action="{{ route('tasks.unfollow', $task) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            توقف دنبال‌کردن
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tasks.follow', $task) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            دنبال‌کردن این وظیفه
                        </button>
                    </form>
                @endif

                @if($task->followers->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($task->followers->take(6) as $follow)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">{{ $follow->user?->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($canManageTask)
                <div class="rounded-2xl bg-white p-5 shadow">
                    <h3 class="mb-3 text-base font-semibold text-gray-800">بروزرسانی وضعیت</h3>
                    <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="pending" @selected($task->status === 'pending')>در انتظار</option>
                            <option value="in_progress" @selected($task->status === 'in_progress')>در حال انجام</option>
                            <option value="done" @selected($task->status === 'done')>انجام‌شده</option>
                        </select>
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            ذخیره وضعیت
                        </button>
                    </form>
                </div>
            @endif

            <div class="rounded-2xl bg-white p-5 shadow">
                <h3 class="mb-3 text-base font-semibold text-gray-800">یادداشت‌های وظیفه</h3>
                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="space-y-3">
                    @csrf
                    <textarea name="body" rows="3" placeholder="یادداشت جدید ثبت کنید"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
                    <p class="text-xs text-gray-500">برای منشن از ایمیل همکار با `@` استفاده کنید. مثال: `@chat1@example.com`</p>
                    <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                        ثبت یادداشت
                    </button>
                </form>

                <div class="mt-4 space-y-3">
                    @forelse($task->comments->sortByDesc('created_at') as $comment)
                        <div class="rounded-xl border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-medium text-gray-800">{{ $comment->user?->name ?? 'کاربر' }}</div>
                                <div class="text-xs text-gray-500">{{ \App\Support\JalaliDate::format($comment->created_at, true) }}</div>
                            </div>
                            <p class="mt-2 text-sm text-gray-700">{{ $comment->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">هنوز یادداشتی ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
