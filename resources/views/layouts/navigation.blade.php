<nav x-data="{ open: false }" class="border-b border-gray-100 bg-white" dir="rtl">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>

                <div class="hidden items-center gap-6 sm:flex">
                    <x-nav-link :href="route('my-work.index')" :active="request()->routeIs('my-work.*')">
                        کارتابل من
                    </x-nav-link>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        داشبورد
                    </x-nav-link>
                    <x-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')">
                        نامه‌ها
                    </x-nav-link>
                    <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                        پیام‌ها
                    </x-nav-link>
                    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                        وظایف
                    </x-nav-link>
                    @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_REPORTS_VIEW))
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                            گزارش‌ها
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_KPIS_VIEW))
                        <x-nav-link :href="route('kpis.index')" :active="request()->routeIs('kpis.*')">
                            KPI
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_WORKLOAD_VIEW))
                        <x-nav-link :href="route('workload.index')" :active="request()->routeIs('workload.*')">
                            Workload
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_EMPLOYEES_MANAGE))
                        <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                            کارمندان
                        </x-nav-link>
                    @endif
                    @if(Auth::user()->canAccessManagementPanel())
                        <x-nav-link :href="route('management.index')" :active="request()->routeIs('management.*')">
                            مدیریت سیستم
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="hidden sm:flex sm:items-center">
                    @php
                        $unread = Auth::user()->unreadNotifications;
                        $unreadCount = $unread->count();
                        $latestUnread = $unread->take(5);
                    @endphp
                    <x-dropdown align="left" width="80">
                        <x-slot name="trigger">
                            <button class="relative inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100">
                                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm6-6V11a6 6 0 1 0-12 0v5l-2 2v1h16v-1l-2-2Z"/>
                                </svg>
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1 -left-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 text-xs text-gray-500">اعلان‌ها</div>
                            <div class="max-h-72 w-80 overflow-y-auto">
                                @forelse($latestUnread as $notification)
                                    @php
                                        $title = $notification->data['title'] ?? 'اعلان';
                                        $body = $notification->data['body_short'] ?? $notification->data['body'] ?? '';
                                        $actionUrl = $notification->data['action_url'] ?? null;
                                    @endphp
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="border-t px-4 py-3">
                                        @csrf
                                        @if($actionUrl)
                                            <input type="hidden" name="redirect" value="{{ $actionUrl }}">
                                        @endif
                                        <button type="submit" class="w-full text-right">
                                            <div class="text-sm font-semibold text-gray-800">{{ $title }}</div>
                                            @if($body)
                                                <div class="mt-1 text-xs text-gray-500">{{ $body }}</div>
                                            @endif
                                        </button>
                                    </form>
                                @empty
                                    <div class="px-4 py-4 text-sm text-gray-500">اعلانی ندارید.</div>
                                @endforelse
                            </div>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.readAll') }}" class="border-t px-4 py-2">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">خواندن همه</button>
                                </form>
                            @endif
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                پروفایل
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    خروج
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="sm:hidden">
                    <button @click="open = ! open" class="rounded-md p-2 text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke="currentColor" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke="currentColor" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t sm:hidden">
        <div class="space-y-1 px-4 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')">داشبورد</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('my-work.index')">کارتابل من</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('letters.index')">نامه‌ها</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('messages.index')">پیام‌ها</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tasks.index')">وظایف</x-responsive-nav-link>
            @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_REPORTS_VIEW))
                <x-responsive-nav-link :href="route('reports.index')">گزارش‌ها</x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_KPIS_VIEW))
                <x-responsive-nav-link :href="route('kpis.index')">KPI</x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_WORKLOAD_VIEW))
                <x-responsive-nav-link :href="route('workload.index')">Workload</x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasPermission(\App\Models\User::PERMISSION_EMPLOYEES_MANAGE))
                <x-responsive-nav-link :href="route('employees.index')">کارمندان</x-responsive-nav-link>
            @endif
            @if(Auth::user()->canAccessManagementPanel())
                <x-responsive-nav-link :href="route('management.index')">مدیریت سیستم</x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t px-4 py-3">
            <div class="font-medium">{{ Auth::user()->name }}</div>
            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">پروفایل</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        خروج
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
