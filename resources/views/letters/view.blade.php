<x-app-layout>
    <x-slot name="header">
        <h2 class="text-right text-xl font-semibold leading-tight text-gray-800">
            کارتابل نامه
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 shadow">
                    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500">شماره دبیرخانه</p>
                            <p class="mt-1 text-lg font-bold text-gray-900">{{ $letter->reference_number ?? '—' }}</p>
                            <h1 class="mt-3 text-2xl font-bold text-gray-800">{{ $letter->subject }}</h1>
                        </div>
                        <a href="{{ route('letters.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            بازگشت به لیست
                        </a>
                    </div>

                    <div class="grid gap-4 rounded-xl bg-gray-50 p-4 text-sm text-gray-700 md:grid-cols-3">
                        <div>
                            <span class="text-gray-500">ثبت‌کننده:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ $letter->user?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">دپارتمان:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ $letter->department->name ?? '—' }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">نوع نامه:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ \App\Models\Letter::typeLabel($letter->type) }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">اولویت:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ \App\Models\Letter::priorityLabel($letter->priority) }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">وضعیت:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ \App\Models\Letter::statusLabel($letter->status) }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">تاریخ ثبت:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ \App\Support\JalaliDate::format($letter->created_at, true) }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">مهلت:</span>
                            <div class="mt-1 font-medium text-gray-800">{{ \App\Support\JalaliDate::format($letter->due_date) }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-base font-semibold text-gray-800">متن نامه</h3>
                        <div class="rounded-xl border border-gray-200 p-4 text-sm leading-8 text-gray-700 whitespace-pre-line">
                            {{ $letter->content ?: $letter->body }}
                        </div>
                    </div>

                    @if($letter->attachments->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="mb-2 text-base font-semibold text-gray-800">پیوست‌ها</h3>
                            <div class="space-y-2">
                                @foreach($letter->attachments as $file)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3">
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $file->name }}</div>
                                            <div class="text-xs text-gray-500">{{ number_format(($file->size ?? 0) / 1024, 0) }} کیلوبایت</div>
                                        </div>
                                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                            دانلود
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">تاریخچه گردش نامه</h3>
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
                                @if(filled($item['response'] ?? null))
                                    <p class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">پاسخ: {{ $item['response'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="space-y-6 lg:col-span-1">
                <div class="rounded-2xl bg-white p-5 shadow">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-800">دنبال کردن نامه</h3>
                        <span class="text-xs text-gray-500">{{ $letter->followers->count() }} دنبال‌کننده</span>
                    </div>

                    @if($isFollowing)
                        <form method="POST" action="{{ route('letters.unfollow', $letter) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                توقف دنبال‌کردن
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('letters.follow', $letter) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                دنبال‌کردن این نامه
                            </button>
                        </form>
                    @endif

                    @if($letter->followers->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($letter->followers->take(6) as $follow)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">{{ $follow->user?->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($canManageStatus)
                    <div class="rounded-2xl bg-white p-5 shadow">
                        <h3 class="mb-3 text-base font-semibold text-gray-800">تغییر وضعیت</h3>
                        <form method="POST" action="{{ route('letters.status', $letter) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="pending" @selected($letter->status === 'pending')>در انتظار</option>
                                <option value="in_progress" @selected($letter->status === 'in_progress')>در حال انجام</option>
                                <option value="completed" @selected($letter->status === 'completed')>تکمیل شده</option>
                                <option value="archived" @selected($letter->status === 'archived')>بایگانی</option>
                            </select>
                            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                ثبت وضعیت
                            </button>
                        </form>
                    </div>
                @endif

                @if($canRequestApproval && $approvalCandidates->isNotEmpty())
                    <div class="rounded-2xl bg-white p-5 shadow">
                        <h3 class="mb-1 text-base font-semibold text-gray-800">درخواست تایید</h3>
                        <p class="mb-3 text-xs text-gray-500">برای مسیر رسمی تایید، نامه را برای یک مدیر یا ادمین ارسال کنید.</p>
                        <form method="POST" action="{{ route('letters.approvals.store', $letter) }}" class="space-y-3">
                            @csrf
                            <select name="approver_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">انتخاب تاییدکننده</option>
                                @foreach($approvalCandidates as $approvalCandidate)
                                    <option value="{{ $approvalCandidate->id }}">{{ $approvalCandidate->name }} - {{ $approvalCandidate->email }}</option>
                                @endforeach
                            </select>
                            <textarea name="request_note" rows="3" placeholder="توضیح درخواست تایید"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('request_note') }}</textarea>
                            <button type="submit" class="w-full rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700">
                                ارسال برای تایید
                            </button>
                        </form>
                    </div>
                @endif

                @if($pendingApprovalForUser)
                    <div class="rounded-2xl bg-white p-5 shadow">
                        <h3 class="mb-1 text-base font-semibold text-gray-800">اقدام روی تایید</h3>
                        <p class="mb-3 text-xs text-gray-500">این نامه برای تایید به شما ارجاع شده است.</p>
                        <form method="POST" action="{{ route('letters.approvals.update', [$letter, $pendingApprovalForUser]) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="approved">تایید شود</option>
                                <option value="rejected">رد شود</option>
                            </select>
                            <textarea name="decision_note" rows="3" placeholder="توضیح نتیجه"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                ثبت نتیجه تایید
                            </button>
                        </form>
                    </div>
                @endif

                @if($canRefer)
                    <div class="rounded-2xl bg-white p-5 shadow">
                        <h3 class="mb-3 text-base font-semibold text-gray-800">ارجاع نامه</h3>
                        <form method="POST" action="{{ route('letters.refer', $letter) }}" class="space-y-3">
                            @csrf
                            <select name="to_user_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">انتخاب دریافت‌کننده</option>
                                @foreach($referrableUsers as $referrableUser)
                                    <option value="{{ $referrableUser->id }}">{{ $referrableUser->name }}</option>
                                @endforeach
                            </select>
                            <textarea name="note" rows="3" placeholder="توضیح ارجاع"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('note') }}</textarea>
                            <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                                ثبت ارجاع
                            </button>
                        </form>
                    </div>
                @endif

                @if($activeReferral)
                    <div class="rounded-2xl bg-white p-5 shadow">
                        <h3 class="mb-1 text-base font-semibold text-gray-800">اقدام روی ارجاع</h3>
                        <p class="mb-3 text-xs text-gray-500">این نامه به شما ارجاع شده و می‌توانید پاسخ ثبت کنید.</p>
                        <form method="POST" action="{{ route('letters.referrals.update', [$letter, $activeReferral]) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="accepted">پذیرش ارجاع</option>
                                <option value="completed">انجام شد</option>
                                <option value="rejected">رد ارجاع</option>
                            </select>
                            <textarea name="response_note" rows="3" placeholder="توضیح پاسخ"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                ثبت پاسخ
                            </button>
                        </form>
                    </div>
                @endif

                <div class="rounded-2xl bg-white p-5 shadow">
                    <h3 class="mb-3 text-base font-semibold text-gray-800">یادداشت‌ها</h3>
                    <form method="POST" action="{{ route('letters.comments.store', $letter) }}" class="space-y-3">
                        @csrf
                        <textarea name="body" rows="3" placeholder="یادداشت جدید ثبت کنید"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
                        <p class="text-xs text-gray-500">برای منشن از ایمیل همکار با `@` استفاده کنید. مثال: `@chat1@example.com`</p>
                        <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                            ثبت یادداشت
                        </button>
                    </form>

                    <div class="mt-4 space-y-3">
                        @forelse($letter->comments->sortByDesc('created_at') as $comment)
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

                <div class="rounded-2xl bg-white p-5 shadow">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-800">سوابق تایید</h3>
                        <span class="text-xs text-gray-500">{{ $letter->approvals->count() }} رکورد</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($letter->approvals as $approval)
                            <div class="rounded-xl border border-gray-200 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-gray-800">{{ \App\Models\LetterApproval::statusLabel($approval->status) }}</div>
                                    <div class="text-xs text-gray-500">{{ \App\Support\JalaliDate::format($approval->requested_at ?? $approval->created_at, true) }}</div>
                                </div>
                                <p class="mt-2 text-sm text-gray-700">
                                    درخواست‌دهنده: {{ $approval->requestedBy?->name ?? '—' }} |
                                    تاییدکننده: {{ $approval->approver?->name ?? '—' }}
                                </p>
                                @if(filled($approval->request_note))
                                    <p class="mt-2 rounded-lg bg-violet-50 px-3 py-2 text-xs text-violet-700">درخواست: {{ $approval->request_note }}</p>
                                @endif
                                @if(filled($approval->decision_note))
                                    <p class="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-700">نتیجه: {{ $approval->decision_note }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">هنوز درخواست تاییدی ثبت نشده است.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
