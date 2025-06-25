<section>
    <div class="mb-4">
        <p class="text-sm text-gray-600">
            アカウントのプロフィール情報とメールアドレスを更新してください。
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">氏名</label>
            <input type="text" 
                   name="name" 
                   id="name"
                   value="{{ old('name', $user->name) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
            <input type="email" 
                   name="email" 
                   id="email"
                   value="{{ old('email', $user->email) }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   required 
                   autocomplete="username">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-800">
                        メールアドレスが確認されていません。

                        <button form="send-verification" class="text-blue-600 hover:text-blue-500 underline">
                            確認メールを再送信する
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            新しい確認リンクがメールアドレスに送信されました。
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                保存
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >保存しました</p>
            @endif
        </div>
    </form>
</section>
