@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl p-4 md:p-6" dir="rtl">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-4">
            <div class="rounded-xl bg-white p-4 shadow">
                <h2 class="mb-4 text-lg font-bold text-gray-800">همکاران</h2>

                <div class="space-y-2">
                    @forelse($contacts as $contact)
                        <a href="{{ route('messages.show', $contact) }}"
                           class="block rounded-lg border px-3 py-2 text-sm transition {{ ($selectedUser?->id === $contact->id) ? 'border-blue-600 bg-blue-50 text-blue-800' : 'border-gray-200 hover:bg-gray-50' }}">
                            <div class="font-semibold">{{ $contact->name }}</div>
                            <div class="text-xs text-gray-500">{{ $contact->email }}</div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">همکاری یافت نشد.</p>
                    @endforelse
                </div>
            </div>
        </aside>

        <section class="lg:col-span-8">
            <div class="rounded-xl bg-white shadow">
                <div class="border-b px-4 py-3">
                    <h1 class="text-base font-bold text-gray-800">
                        {{ $selectedUser ? 'گفتگو با '.$selectedUser->name : 'چت سازمانی' }}
                    </h1>
                </div>

                <div class="h-[420px] overflow-y-auto p-4">
                    @if($selectedUser)
                        <div class="space-y-3">
                            @forelse($messages as $message)
                                @php
                                    $isMine = $message->sender_id === auth()->id();
                                @endphp
                                <div class="flex {{ $isMine ? 'justify-start' : 'justify-end' }}">
                                    <div class="max-w-[85%] rounded-xl px-3 py-2 text-sm {{ $isMine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                        @if(filled($message->message))
                                            <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                                        @endif

                                        @if(filled($message->attachment_path))
                                            @if($message->isImage())
                                                <a href="{{ route('messages.preview', $message) }}" target="_blank" class="mt-2 block">
                                                    <img src="{{ route('messages.preview', $message) }}" alt="{{ $message->attachment_name }}"
                                                         class="max-h-56 rounded-xl border border-white/20 object-contain {{ $isMine ? 'bg-blue-500/30' : 'bg-white' }}">
                                                </a>
                                            @elseif($message->isVideo())
                                                <video controls class="mt-2 max-h-64 w-full rounded-xl bg-black">
                                                    <source src="{{ route('messages.preview', $message) }}" type="{{ $message->attachment_mime }}">
                                                    مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
                                                </video>
                                            @elseif($message->isAudio())
                                                <audio controls class="mt-2 w-full">
                                                    <source src="{{ route('messages.preview', $message) }}" type="{{ $message->attachment_mime }}">
                                                    مرورگر شما از پخش صوت پشتیبانی نمی‌کند.
                                                </audio>
                                            @elseif($message->isPdf())
                                                <div class="mt-2 overflow-hidden rounded-xl border {{ $isMine ? 'border-blue-300/40 bg-blue-500/20' : 'border-gray-200 bg-white' }}">
                                                    <iframe src="{{ route('messages.preview', $message) }}" class="h-72 w-full" title="{{ $message->attachment_name }}"></iframe>
                                                </div>
                                            @endif

                                            <a href="{{ route('messages.download', $message) }}"
                                               class="mt-2 inline-block text-xs underline {{ $isMine ? 'text-blue-100' : 'text-blue-700' }}">
                                                فایل: {{ $message->attachment_name ?? 'دانلود فایل' }}
                                            </a>
                                            @if(filled($message->attachment_size))
                                                <div class="mt-1 text-[10px] {{ $isMine ? 'text-blue-100' : 'text-gray-500' }}">
                                                    حجم: {{ number_format(($message->attachment_size ?? 0) / 1024, 0) }} کیلوبایت
                                                </div>
                                            @endif
                                        @endif

                                        <div class="mt-1 text-[10px] {{ $isMine ? 'text-blue-100' : 'text-gray-500' }}">
                                            {{ \App\Support\JalaliDate::format($message->created_at, true) }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="py-8 text-center text-sm text-gray-500">هنوز پیامی رد و بدل نشده است.</p>
                            @endforelse
                        </div>
                    @else
                        <p class="py-8 text-center text-sm text-gray-500">یک همکار را از لیست انتخاب کنید.</p>
                    @endif
                </div>

                @if($selectedUser)
                    <form action="{{ route('messages.store', $selectedUser) }}" method="POST" enctype="multipart/form-data" class="border-t p-4 space-y-3">
                        @csrf

                        @if ($errors->any())
                            <div class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3" x-data="{ openEmoji: false, emojis: ['😀','😁','😂','😍','🥰','😎','🤝','👏','🙏','👍','👀','🔥','✅','📌','📎','📞','📝','⏰','🎯','🚀'] }">
                            <div class="mb-2 flex items-center justify-between">
                                <button type="button"
                                        @click="openEmoji = !openEmoji"
                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <span class="text-lg">😀</span>
                                </button>
                            </div>

                            <div x-show="openEmoji" x-transition class="mb-3 flex flex-wrap gap-2 rounded-lg border border-gray-200 bg-white p-3">
                                <template x-for="emoji in emojis" :key="emoji">
                                    <button
                                        type="button"
                                        @click="
                                            const textarea = $refs.messageInput;
                                            const start = textarea.selectionStart ?? textarea.value.length;
                                            const end = textarea.selectionEnd ?? textarea.value.length;
                                            const value = textarea.value;
                                            textarea.value = value.slice(0, start) + emoji + ' ' + value.slice(end);
                                            textarea.focus();
                                            const position = start + emoji.length + 1;
                                            textarea.setSelectionRange(position, position);
                                        "
                                        class="rounded-lg border border-gray-200 px-2 py-1 text-xl hover:bg-gray-50"
                                        x-text="emoji"
                                    ></button>
                                </template>
                            </div>

                            <textarea x-ref="messageInput" name="message" rows="3" placeholder="پیام خود را بنویسید..."
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('message') }}</textarea>
                        </div>

                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <input type="file" name="attachment"
                                   class="block w-full text-sm text-gray-700 file:ml-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">

                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                ارسال
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">هر نوع فایل مجاز است. سقف پروژه: ۱۰۰ مگابایت. فایل‌ها فقط برای فرستنده و گیرنده قابل دانلود هستند.</p>
                    </form>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
