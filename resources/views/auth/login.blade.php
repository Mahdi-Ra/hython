<x-guest-layout>
    <div dir="rtl" class="text-right">

        <!-- وضعیت سشن -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- ایمیل -->
            <div>
                <x-input-label for="email" value="ایمیل" />
                <x-text-input id="email" class="block mt-1 w-full"
                              type="email"
                              name="email"
                              :value="old('email')"
                              required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- پسورد -->
            <div class="mt-4">
                <x-input-label for="password" value="رمز عبور" />

                <x-text-input id="password" class="block mt-1 w-full"
                              type="password"
                              name="password"
                              required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- مرا به خاطر بسپار -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                           name="remember">
                    <span class="mr-2 text-sm text-gray-600">
                        مرا به خاطر بسپار
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4">

                @if (Route::has('password.request'))
                    <a class="text-sm text-gray-600 hover:text-gray-900"
                       href="{{ route('password.request') }}">
                        فراموشی رمز عبور؟
                    </a>
                @endif

                <x-primary-button class="mr-3">
                    ورود به پنل
                </x-primary-button>
            </div>

        </form>
    </div>
</x-guest-layout>
