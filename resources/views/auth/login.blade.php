<x-login-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="px-4 sm:px-6">
        @csrf

        <!-- Email Address -->
        <div class="mb-8">
            <x-underlined-input label="{{ __('Email Address') }}" name="email" type="email" :value="old('email')" required />
        </div>

        <!-- Password -->
        <div class="mb-8">
            <x-underlined-input label="{{ __('Password') }}" name="password" type="password" required />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember Me') }}</span>
            </label>
        </div>

        <div class="mt-6 w-full flex flex-col items-center">
            <div class="w-full max-w-md flex justify-center">
                <x-primary-button class="w-48 flex justify-center bg-emerald-600">
                    {{ __('Login') }}
                </x-primary-button>
            </div>

            @if (Route::has('password.request'))
            <div class="mt-4 text-center">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
            </div>
            @endif
        </div>
    </form>
</x-login-layout>
