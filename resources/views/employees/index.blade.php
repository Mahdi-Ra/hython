@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" dir="rtl">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">مدیریت کارمندان</h2>
        <a href="{{ route('employees.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow">
            افزودن کارمند
        </a>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-right">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">نام</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">ایمیل</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">نقش</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">دپارتمان</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $employee->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $employee->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                                @if($employee->role === 'admin')
                                    ادمین
                                @elseif($employee->role === 'manager')
                                    مدیر
                                @else
                                    کارمند
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-800">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap flex gap-2 justify-center">
                            <a href="{{ route('employees.edit', $employee->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-sm">
                               ویرایش
                            </a>
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow text-sm">
                                    حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">
                            کارمندی یافت نشد
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
