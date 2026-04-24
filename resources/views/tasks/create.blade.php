@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl p-4 md:p-6" dir="rtl">
    <div class="rounded-xl bg-white p-5 shadow">
        <h1 class="mb-4 text-xl font-bold text-gray-800">ایجاد وظیفه</h1>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4" data-jalali-form>
            @csrf

            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-gray-700">عنوان</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-gray-700">توضیحات</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="assigned_to" class="mb-1 block text-sm font-medium text-gray-700">مسئول</label>
                <select id="assigned_to" name="assigned_to"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">انتخاب کنید</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('assigned_to') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="priority" class="mb-1 block text-sm font-medium text-gray-700">اولویت</label>
                    <select id="priority" name="priority"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="normal" @selected(old('priority', 'normal') === 'normal')>عادی</option>
                        <option value="high" @selected(old('priority') === 'high')>بالا</option>
                        <option value="urgent" @selected(old('priority') === 'urgent')>فوری</option>
                        <option value="low" @selected(old('priority') === 'low')>پایین</option>
                    </select>
                </div>
                <div>
                    <label for="due_date" class="mb-1 block text-sm font-medium text-gray-700">مهلت</label>
                    <input type="hidden" id="due_date" name="due_date" value="{{ old('due_date') }}">
                    <input type="text" data-jalali-visible="due_date" placeholder="مثلاً ۱۴۰۵/۰۱/۳۰"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('due_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    انصراف
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    ثبت وظیفه
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
