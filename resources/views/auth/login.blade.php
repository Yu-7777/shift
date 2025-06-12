<x-login-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-8">
            <x-underlined-input label="{{ __('Email Address') }}" name="email" id="email" type="email" required />
        </div>

        <!-- Password -->
        <div class="mb-8">
            <x-underlined-input label="{{ __('Password') }}" name="password" id="password" type="password" required />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember Me') }}</span>
            </label>
        </div>

            <div class="mt-6 w-full flex justify-center">
                <x-primary-button class="flex justify-center">
                    {{ __('Login') }}
                </x-primary-button>

                @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
            </div>
            @endif
        </div>
    </form>
</x-login-layout>
