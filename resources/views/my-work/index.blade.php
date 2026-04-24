@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">کارتابل من</h1>
        <p class="mt-1 text-sm text-gray-500">همه کارهای شخصی، ارجاعات، سررسیدها و اعلان‌های شما در یک صفحه</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">وظایف باز</div>
            <div class="mt-2 text-3xl font-bold text-gray-800">{{ $openTasksCount }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">وظایف نزدیک سررسید</div>
            <div class="mt-2 text-3xl font-bold text-amber-600">{{ $dueSoonTasks }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">وظایف معوق</div>
            <div class="mt-2 text-3xl font-bold text-red-600">{{ $overdueTasks }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow">
            <div class="text-sm text-gray-500">ارجاع‌های فعال</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ $activeReferralsCount }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">اولویت‌های امروز</h2>
                <div class="space-y-3">
                    @forelse($upcomingItems as $item)
                        <a href="{{ $item['url'] ?? '#' }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-gray-800">{{ $item['title'] }}</div>
                                <span class="text-xs text-gray-500">{{ $item['type'] === 'task' ? 'وظیفه' : 'نامه' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                                <span>مهلت: {{ \App\Support\JalaliDate::format($item['due_date']) }}</span>
                                <span>{{ $item['status'] }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">مورد سررسیدداری برای شما ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">وظایف من</h2>
                <div class="space-y-3">
                    @forelse($myTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-gray-800">{{ $task->title }}</div>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">{{ \App\Models\Task::statusLabel($task->status) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                                <span>مهلت: {{ \App\Support\JalaliDate::format($task->due_date) }}</span>
                                @if($task->letter)
                                    <span>{{ $task->letter->reference_number ?? '—' }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">فعلاً وظیفه بازی ندارید.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">ارجاع‌های من</h2>
                <div class="space-y-3">
                    @forelse($myReferrals as $referral)
                        <a href="{{ route('letters.view', $referral->letter) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-gray-800">{{ $referral->letter?->subject ?? 'نامه' }}</div>
                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs text-blue-700">{{ \App\Models\LetterReferral::statusLabel($referral->status) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                                <span>{{ $referral->letter?->reference_number ?? '—' }}</span>
                                <span>{{ \App\Support\JalaliDate::format($referral->letter?->due_date) }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">ارجاع فعالی ندارید.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6 lg:col-span-1">
            <div class="rounded-xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">نامه‌های در جریان من</h2>
                <div class="space-y-3">
                    @forelse($myLetters as $letter)
                        <a href="{{ route('letters.view', $letter) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="font-semibold text-gray-800">{{ $letter->subject }}</div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs text-gray-500">
                                <span>{{ $letter->reference_number ?? '—' }}</span>
                                <span>{{ \App\Models\Letter::statusLabel($letter->status) }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">نامه بازی برای شما نیست.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">آخرین اعلان‌ها</h2>
                <div class="space-y-3">
                    @forelse($latestNotifications as $notification)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="font-semibold text-gray-800">{{ $notification->data['title'] ?? 'اعلان' }}</div>
                            @if(! empty($notification->data['body']))
                                <div class="mt-1 text-sm text-gray-600">{{ $notification->data['body'] }}</div>
                            @endif
                            <div class="mt-2 text-xs text-gray-500">{{ \App\Support\JalaliDate::format($notification->created_at, true) }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">اعلانی ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
