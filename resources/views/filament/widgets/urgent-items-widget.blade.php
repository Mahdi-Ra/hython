@php
    use App\Filament\Resources\LetterResource;
    use App\Filament\Resources\TaskResource;
    use App\Models\Letter;
    use App\Models\Task;
@endphp

<x-filament::section>
        <div class="fi-wi-widget grid gap-6 md:grid-cols-2" dir="rtl">
            {{-- نامه‌های فوری --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-950 mb-3">
                    نامه‌های فوری
                </h3>
                @if($urgentLetters->isEmpty())
                    <p class="text-sm text-gray-500 py-4">موردی یافت نشد.</p>
                @else
                    <ul class="divide-y divide-gray-200 space-y-0">
                        @foreach($urgentLetters as $letter)
                            <li class="py-3 first:pt-0">
                                <a href="{{ LetterResource::getUrl('view', ['record' => $letter]) }}"
                                   class="flex items-start justify-between gap-2 text-primary-600 hover:underline">
                                    <span class="text-sm font-medium line-clamp-1">{{ $letter->subject }}</span>
                                    <span class="shrink-0 text-xs fi-badge fi-color-danger fi-badge-danger px-2 py-0.5 rounded-md">
                                        {{ $letter->priority === Letter::PRIORITY_URGENT ? 'فوری' : 'بالا' }}
                                    </span>
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    مهلت: {{ $letter->due_date?->format('Y/m/d') ?? '—' }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- وظایف فوری --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-950 mb-3">
                    وظایف فوری
                </h3>
                @if($urgentTasks->isEmpty())
                    <p class="text-sm text-gray-500 py-4">موردی یافت نشد.</p>
                @else
                    <ul class="divide-y divide-gray-200 space-y-0">
                        @foreach($urgentTasks as $task)
                            <li class="py-3 first:pt-0">
                                <a href="{{ TaskResource::getUrl('view', ['record' => $task]) }}"
                                   class="flex items-start justify-between gap-2 text-primary-600 hover:underline">
                                    <span class="text-sm font-medium line-clamp-1">{{ $task->title }}</span>
                                    <span class="shrink-0 text-xs fi-badge fi-color-danger fi-badge-danger px-2 py-0.5 rounded-md">
                                        {{ $task->priority === Task::PRIORITY_URGENT ? 'فوری' : 'بالا' }}
                                    </span>
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    مهلت: {{ $task->due_date?->format('Y/m/d') ?? '—' }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
</x-filament::section>
