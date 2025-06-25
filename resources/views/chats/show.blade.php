<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if($chat->type === 'dm')
                    {{ $chat->users->where('id', '!=', auth()->id())->first()->name ?? 'Unknown User' }}
                @else
                    {{ $chat->name }}
                @endif
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    参加者: {{ $chatMembers->count() }}人
                </span>
                <a href="{{ route('chats.index') }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    チャット一覧に戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                @if(session('success'))
                    <div class="p-4 bg-green-100 border-b border-green-400 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-red-100 border-b border-red-400 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- チャットメンバー表示 -->
                @if($chat->type === 'group')
                    <div class="p-4 bg-gray-50 border-b">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">チャットメンバー</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($chatMembers as $member)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $member->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- メッセージ表示エリア -->
                <div class="h-96 overflow-y-auto p-4 space-y-4" id="messagesContainer">
                    @if($messages->count() > 0)
                        @foreach($messages as $message)
                            <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                    @if($message->user_id !== auth()->id())
                                        <p class="text-xs font-medium mb-1 {{ $message->user_id === auth()->id() ? 'text-blue-100' : 'text-gray-600' }}">
                                            {{ $message->user->name }}
                                        </p>
                                    @endif
                                    <p class="text-sm break-words">{{ $message->body }}</p>
                                    <p class="text-xs mt-1 {{ $message->user_id === auth()->id() ? 'text-blue-100' : 'text-gray-500' }}">
                                        {{ $message->created_at->format('m/d H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">メッセージがありません</h3>
                            <p class="mt-1 text-sm text-gray-500">最初のメッセージを送信してみましょう。</p>
                        </div>
                    @endif
                </div>

                <!-- ページネーション -->
                @if($messages->hasPages())
                    <div class="px-4 py-3 border-t">
                        {{ $messages->links() }}
                    </div>
                @endif

                <!-- メッセージ送信フォーム -->
                <div class="p-4 border-t bg-gray-50">
                    <form action="{{ route('chats.send-message', $chat) }}" method="POST" class="flex space-x-2">
                        @csrf
                        <div class="flex-1">
                            <textarea 
                                name="body" 
                                placeholder="メッセージを入力..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                rows="2"
                                required
                                maxlength="1000"
                                onkeypress="handleKeyPress(event)"
                            ></textarea>
                            @error('body')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            送信
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // メッセージコンテナを下にスクロール
        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        // ページ読み込み時に下にスクロール
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });

        // Enterキーでの送信（Shift+Enterで改行）
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.target.closest('form').submit();
            }
        }

        // メッセージ送信後にスクロール
        @if(session('success'))
            setTimeout(scrollToBottom, 100);
        @endif

        // 定期的にメッセージを更新（簡単な実装）
        // 実際のプロジェクトではWebSocketやServer-Sent Eventsを使用することを推奨
        let lastMessageCount = {{ $messages->count() }};
        
        function checkForNewMessages() {
            // この実装は簡単な例です。実際のプロジェクトでは適切なAPIエンドポイントを作成してください
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMessagesContainer = doc.getElementById('messagesContainer');
                const currentMessagesContainer = document.getElementById('messagesContainer');
                
                if (newMessagesContainer && currentMessagesContainer) {
                    const newMessageCount = newMessagesContainer.children.length;
                    if (newMessageCount > lastMessageCount) {
                        currentMessagesContainer.innerHTML = newMessagesContainer.innerHTML;
                        scrollToBottom();
                        lastMessageCount = newMessageCount;
                    }
                }
            })
            .catch(error => {
                console.error('メッセージ更新エラー:', error);
            });
        }

        // 5秒ごとに新しいメッセージをチェック
        setInterval(checkForNewMessages, 5000);
    </script>
</x-app-layout>