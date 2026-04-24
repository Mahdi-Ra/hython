@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl p-4 md:p-6" dir="rtl">
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-800">ویرایش دپارتمان</h1>
            <a href="{{ route('management.departments.index') }}" class="text-sm text-blue-600 hover:underline">بازگشت</a>
        </div>

        <form action="{{ route('management.departments.update', $department) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm text-gray-700">نام</label>
                <input name="name" value="{{ old('name', $department->name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-sm text-gray-700">نامک</label>
                <input name="slug" value="{{ old('slug', $department->slug) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-sm text-gray-700">والد</label>
                <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">بدون والد</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $department->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm text-gray-700">توضیحات</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('description', $department->description) }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $department->is_active))>
                فعال
            </label>
            <div class="flex justify-end gap-2">
                <a href="{{ route('management.departments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">انصراف</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">ذخیره</button>
            </div>
        </form>
    </div>
</div>
@endsection
