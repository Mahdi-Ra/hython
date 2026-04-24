@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl p-4 md:p-6" dir="rtl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">مدیریت دپارتمان‌ها</h1>
            <p class="mt-1 text-sm text-gray-500">ایجاد و ویرایش واحدهای سازمانی</p>
        </div>
        <a href="{{ route('management.index') }}" class="text-sm text-blue-600 hover:underline">بازگشت</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">ایجاد دپارتمان</h2>
            <form action="{{ route('management.departments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm text-gray-700">نام</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-gray-700">نامک</label>
                    <input name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-gray-700">والد</label>
                    <select name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">بدون والد</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm text-gray-700">توضیحات</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('description') }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" checked>
                    فعال
                </label>
                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">ثبت</button>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="overflow-x-auto rounded-xl bg-white shadow">
                <table class="min-w-full text-right text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">نام</th>
                            <th class="px-4 py-3">نامک</th>
                            <th class="px-4 py-3">والد</th>
                            <th class="px-4 py-3">وضعیت</th>
                            <th class="px-4 py-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($departments as $department)
                            <tr>
                                <td class="px-4 py-3 text-gray-800">{{ $department->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $department->slug }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $department->parent->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs {{ $department->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $department->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="{{ route('management.departments.edit', $department) }}" class="rounded-lg bg-blue-600 px-3 py-1 text-xs text-white hover:bg-blue-700">ویرایش</a>
                                        <form action="{{ route('management.departments.destroy', $department) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">دپارتمانی وجود ندارد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($departments->hasPages())
                <div class="mt-4">{{ $departments->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
