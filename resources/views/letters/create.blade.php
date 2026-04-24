@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl p-4 md:p-6" dir="rtl">
    <div class="rounded-2xl bg-white p-6 shadow">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">ثبت نامه جدید</h2>
                <p class="mt-1 text-sm text-gray-500">شماره دبیرخانه بعد از ثبت، به‌صورت خودکار تولید می‌شود.</p>
            </div>
            <a href="{{ route('letters.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                بازگشت
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('letters.store') }}" enctype="multipart/form-data" class="space-y-5" data-jalali-form>
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="subject" class="mb-1 block text-sm font-medium text-gray-700">عنوان نامه</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                </div>

                @if($canPickDepartment)
                    <div>
                        <label for="department_id" class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                        <select name="department_id" id="department_id" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">انتخاب کنید</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">دپارتمان</label>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            {{ $userDepartment->name ?? '—' }}
                        </div>
                        <input type="hidden" name="department_id" value="{{ $userDepartment->id ?? '' }}">
                    </div>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-gray-700">نوع نامه</label>
                    <select name="type" id="type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="internal" @selected(old('type', 'internal') === 'internal')>داخلی</option>
                        <option value="external" @selected(old('type') === 'external')>خارجی</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="mb-1 block text-sm font-medium text-gray-700">اولویت</label>
                    <select name="priority" id="priority"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="low" @selected(old('priority') === 'low')>پایین</option>
                        <option value="normal" @selected(old('priority', 'normal') === 'normal')>عادی</option>
                        <option value="high" @selected(old('priority') === 'high')>بالا</option>
                        <option value="urgent" @selected(old('priority') === 'urgent')>فوری</option>
                    </select>
                </div>

                <div>
                    <label for="due_date_visible" class="mb-1 block text-sm font-medium text-gray-700">مهلت رسیدگی</label>
                    <input type="hidden" id="due_date" name="due_date" value="{{ old('due_date') }}">
                    <input type="text" id="due_date_visible" data-jalali-visible="due_date" placeholder="مثلاً ۱۴۰۵/۰۱/۱۵"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label for="content" class="mb-1 block text-sm font-medium text-gray-700">متن نامه</label>
                <textarea name="content" id="content" rows="8"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                          required>{{ old('content') }}</textarea>
            </div>

            <div>
                <label for="attachments" class="mb-1 block text-sm font-medium text-gray-700">پیوست‌ها</label>
                <input type="file" id="attachments" name="attachments[]" multiple
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 file:ml-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-500">می‌توانید چند فایل را همزمان ضمیمه کنید. حداکثر حجم هر فایل ۱۰ مگابایت است.</p>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('letters.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    انصراف
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    ثبت نامه
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
