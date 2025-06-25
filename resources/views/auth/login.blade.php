<x-login-layout 
    :description="'アカウントにログインしてください'"
    :title="'ログイン'"
    :footer-text="'アカウントをお持ちでない方は'"
    :footer-link="route('register')"
    :footer-link-text="'こちらから登録'">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autofocus>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
            <input type="password" 
                   name="password" 
                   id="password"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" 
                       name="remember">
                <span class="ml-2 text-sm text-gray-600">ログイン状態を保持する</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-500" href="{{ route('password.request') }}">
                    パスワードを忘れた方はこちら
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                ログイン
            </button>
        </div>
    </form>
</x-login-layout>
