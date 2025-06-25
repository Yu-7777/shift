<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('チャット') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- チャット作成ボタン -->
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-medium">チャット一覧</h3>
                        <div class="space-x-2">
                            <button 
                                onclick="openUserSearchModal()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                新しいDM
                            </button>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($chats->count() > 0)
                        <div class="space-y-4">
                            @foreach($chats as $chat)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <a href="{{ route('chats.show', $chat) }}" class="block">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        @if($chat->type === 'dm')
                                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                                <span class="text-white font-medium">
                                                                    {{ $chat->users->where('id', '!=', auth()->id())->first()->name[0] ?? 'U' }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                                                <span class="text-white font-medium">G</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center justify-between">
                                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                                @if($chat->type === 'dm')
                                                                    {{ $chat->users->where('id', '!=', auth()->id())->first()->name ?? 'Unknown User' }}
                                                                @else
                                                                    {{ $chat->name }}
                                                                @endif
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $chat->updated_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        <p class="text-sm text-gray-500 truncate">
                                                            @if($chat->messages->count() > 0)
                                                                {{ $chat->messages->first()->body }}
                                                            @else
                                                                チャットを開始しましょう
                                                            @endif
                                                        </p>
                                                        <p class="text-xs text-gray-400">
                                                            参加者: {{ $chat->users->count() }}人
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <form action="{{ route('chats.destroy', $chat) }}" method="POST" 
                                                  onsubmit="return confirm('このチャットを削除しますか？')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- ページネーション -->
                        <div class="mt-6">
                            {{ $chats->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">チャットがありません</h3>
                            <p class="mt-1 text-sm text-gray-500">新しいDMを開始してみましょう。</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ユーザー検索モーダル -->
    <div id="userSearchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">ユーザーを検索</h3>
                <div class="mb-4">
                    <input 
                        type="text" 
                        id="userSearch" 
                        placeholder="ユーザー名で検索..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        onkeyup="searchUsers()"
                    >
                </div>
                <div id="searchResults" class="max-h-60 overflow-y-auto space-y-2">
                    <!-- 検索結果がここに表示されます -->
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button 
                        onclick="closeUserSearchModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                    >
                        キャンセル
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openUserSearchModal() {
            document.getElementById('userSearchModal').classList.remove('hidden');
            document.getElementById('userSearch').focus();
        }

        function closeUserSearchModal() {
            document.getElementById('userSearchModal').classList.add('hidden');
            document.getElementById('userSearch').value = '';
            document.getElementById('searchResults').innerHTML = '';
        }

        async function searchUsers() {
            const query = document.getElementById('userSearch').value;
            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`{{ route('chats.search-users') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const users = await response.json();
                const resultsDiv = document.getElementById('searchResults');
                
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-gray-500 text-center py-4">ユーザーが見つかりません</p>';
                    return;
                }

                resultsDiv.innerHTML = users.map(user => `
                    <div class="flex items-center justify-between p-2 border rounded hover:bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">${user.name[0]}</span>
                            </div>
                            <span class="text-sm font-medium">${user.name}</span>
                        </div>
                        <form action="/chats/dm/${user.id}" method="POST" class="inline">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                            <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                DM開始
                            </button>
                        </form>
                    </div>
                `).join('');
            } catch (error) {
                console.error('ユーザー検索エラー:', error);
                document.getElementById('searchResults').innerHTML = '<p class="text-red-500 text-center py-4">検索エラーが発生しました</p>';
            }
        }

        // モーダル外クリックで閉じる
        document.getElementById('userSearchModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserSearchModal();
            }
        });
    </script>
</x-app-layout>