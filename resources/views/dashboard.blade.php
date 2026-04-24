<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right">
            داشبورد 
        </h2>
    </x-slot>

    @php
    $urgentLetters = \App\Models\Letter::query()->where('priority', \App\Models\Letter::PRIORITY_URGENT)->get();
    $urgentTasks = \App\Models\Task::query()->where('priority', \App\Models\Task::PRIORITY_URGENT)->get();
@endphp


    <div class="container mx-auto px-4 md:px-8 mt-6" dir="rtl">
        {{-- دکمه‌ها --}}
        <div class="flex justify-start gap-3 mb-6">
            <a href="{{ route('letters.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                ایجاد نامه جدید
            </a>
            <button class="px-4 py-2 bg-green-500 text-white rounded-md shadow hover:bg-green-600 transition">
                ارسال پیام
            </button>
        </div>

        {{-- بخش نامه‌ها و وظایف فوری --}}
        <div class="grid gap-6 md:grid-cols-2">
            {{-- نامه‌های فوری --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-950 mb-3">نامه‌های فوری</h3>
                @if($urgentLetters->isEmpty())
                    <p class="text-sm text-gray-500 py-4">موردی یافت نشد.</p>
                @else
                    <ul class="divide-y divide-gray-200 space-y-0">
                        @foreach($urgentLetters as $letter)
                            <li class="py-3 first:pt-0">
                                <span class="text-sm font-medium line-clamp-1">{{ $letter->subject }}</span>
                                <p class="text-xs text-gray-500 mt-1">
                                    مهلت: {{ \App\Support\JalaliDate::format($letter->due_date) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- وظایف فوری --}}
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-950 mb-3">وظایف فوری</h3>
                @if($urgentTasks->isEmpty())
                    <p class="text-sm text-gray-500 py-4">موردی یافت نشد.</p>
                @else
                    <ul class="divide-y divide-gray-200 space-y-0">
                        @foreach($urgentTasks as $task)
                            <li class="py-3 first:pt-0">
                                <span class="text-sm font-medium line-clamp-1">{{ $task->title }}</span>
                                <p class="text-xs text-gray-500 mt-1">
                                    مهلت: {{ \App\Support\JalaliDate::format($task->due_date) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- باکس آخرین پیام‌ها --}}
        <div class="mt-6 bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-950 mb-3">آخرین پیام‌ها</h3>
            <ul class="divide-y divide-gray-200 space-y-0">
                <li class="py-3 first:pt-0">
                    <span class="text-sm font-medium">پیام از همکار</span>
                    <p class="text-xs text-gray-500 mt-1">سلام! لطفاً گزارش هفته گذشته را بررسی کن.</p>
                    <p class="text-xs text-gray-400 mt-1">ارسال شده در: 1404/12/20</p>
                </li>
            </ul>
        </div>
    </div>
</x-app-layout>
