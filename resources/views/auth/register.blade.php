<x-login-layout 
    :description="'新しいアカウントを作成してください'"
    :title="'アカウント作成'"
    :footer-text="'既にアカウントをお持ちの方は'"
    :footer-link="route('login')"
    :footer-link-text="'こちらからログイン'">

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">氏名</label>
            <input type="text" 
                   name="name" 
                   id="name"
                   value="{{ old('name') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autocomplete="username">
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
                   required 
                   autocomplete="new-password">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">パスワード確認</label>
            <input type="password" 
                   name="password_confirmation" 
                   id="password_confirmation"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autocomplete="new-password">
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                アカウント作成
            </button>
        </div>
    </form>
</x-login-layout>
