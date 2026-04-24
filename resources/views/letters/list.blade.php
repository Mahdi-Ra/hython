@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="rounded-2xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">دبیرخانه نامه‌ها</h3>
                <p class="mt-1 text-sm text-gray-500">جستجو و پیگیری نامه‌ها بر اساس شماره، نوع، وضعیت و مهلت</p>
            </div>
            @if(auth()->user()->hasPermission(\App\Models\User::PERMISSION_LETTERS_CREATE))
                <a href="{{ route('letters.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                    ثبت نامه جدید
                </a>
            @endif
        </div>

        <form method="GET" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-8" data-jalali-filter-form>
            <input name="q" value="{{ request('q') }}" placeholder="جستجو در عنوان یا شماره دبیرخانه"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            <select name="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">همه نوع‌ها</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ \App\Models\Letter::typeLabel($type) }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">همه وضعیت‌ها</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Letter::statusLabel($status) }}</option>
                @endforeach
            </select>
            <select name="priority" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">همه اولویت‌ها</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ \App\Models\Letter::priorityLabel($priority) }}</option>
                @endforeach
            </select>
            <select name="department_id" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">همه دپارتمان‌ها</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            <input type="hidden" name="from" value="{{ request('from') }}">
            <input type="hidden" name="to" value="{{ request('to') }}">
            <input type="text" data-jalali-visible="from" placeholder="از تاریخ ۱۴۰۵/۰۱/۰۱" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <input type="text" data-jalali-visible="to" placeholder="تا تاریخ ۱۴۰۵/۰۱/۳۰" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <div class="flex gap-3 md:col-span-2">
                <select name="overdue" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">همه مهلت‌ها</option>
                    <option value="1" @selected(request('overdue') === '1')>معوق</option>
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800">اعمال فیلتر</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-right text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-gray-500">شماره دبیرخانه</th>
                        <th class="px-4 py-3 text-gray-500">عنوان</th>
                        <th class="px-4 py-3 text-gray-500">نوع</th>
                        <th class="px-4 py-3 text-gray-500">ثبت‌کننده</th>
                        <th class="px-4 py-3 text-gray-500">دپارتمان</th>
                        <th class="px-4 py-3 text-gray-500">اولویت</th>
                        <th class="px-4 py-3 text-gray-500">وضعیت</th>
                        <th class="px-4 py-3 text-gray-500">تاریخ ثبت</th>
                        <th class="px-4 py-3 text-gray-500">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($letters as $letter)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $letter->reference_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $letter->subject }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \App\Models\Letter::typeLabel($letter->type) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $letter->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $letter->department->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \App\Models\Letter::priorityLabel($letter->priority) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \App\Models\Letter::statusLabel($letter->status) }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \App\Support\JalaliDate::format($letter->created_at) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('letters.view', $letter) }}" class="text-blue-600 hover:underline">
                                    مشاهده
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">نامه‌ای برای این فیلترها پیدا نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($letters->hasPages())
            <div class="mt-4">
                {{ $letters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
