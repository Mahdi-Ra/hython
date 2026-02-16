<nav x-data="{ open: false }" class="bg-white border-b border-gray-100" dir="rtl">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- RIGHT SIDE (لوگو) -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-6">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        داشبورد
                    </x-nav-link>
                </div>
            </div>

            <!-- LEFT SIDE -->
            <div class="flex items-center gap-6">

                

                <!-- User Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 transition">
                                <div>{{ Auth::user()->name }}</div>

                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
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
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    خروج
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="sm:hidden">
                    <button @click="open = ! open"
                        class="p-2 rounded-md text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }"
                                class="inline-flex"
                                stroke="currentColor" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }"
                                class="hidden"
                                stroke="currentColor" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- MOBILE -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <x-responsive-nav-link :href="route('dashboard')">
                داشبورد
            </x-responsive-nav-link>
        </div>

        <div class="border-t px-4 py-3">
            <div class="font-medium">{{ Auth::user()->name }}</div>
            <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    پروفایل
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        خروج
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
