<section class="space-y-6">
    <div class="mb-4">
        <p class="text-sm text-gray-600">
            アカウントを削除すると、すべてのリソースとデータが永続的に削除されます。アカウントを削除する前に、保持したいデータや情報をダウンロードしてください。
        </p>
    </div>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors"
    >アカウントを削除</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                本当にアカウントを削除しますか？
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                アカウントを削除すると、すべてのリソースとデータが永続的に削除されます。アカウントを永続的に削除することを確認するため、パスワードを入力してください。
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">パスワード</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="パスワード"
                />
                @error('password', 'userDeletion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        x-on:click="$dispatch('close')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    キャンセル
                </button>

                <button type="submit" 
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    アカウントを削除
                </button>
            </div>
        </form>
    </x-modal>
</section>
