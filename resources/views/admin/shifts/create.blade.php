<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                シフト作成 - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.availabilities.index', $group) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    可用性一覧
                </a>
                <a href="{{ route('admin.shifts.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    シフト一覧
                </a>
                <a href="{{ route('groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- 説明 -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-800 mb-2">シフト作成の流れ</h3>
                <ol class="text-sm text-blue-700 list-decimal list-inside space-y-1">
                    <li>シフトの日付と時間を決定します</li>
                    <li>その時間に対応可能なメンバーが自動で表示されます</li>
                    <li>表示されたメンバーの中から1人を選択します</li>
                    <li>シフトが作成され、選択されたメンバーに通知されます</li>
                </ol>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- シフト時間設定 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">1. シフト時間を設定</h3>
                        
                        <form id="searchForm">
                            <div class="space-y-4">
                                <!-- 日付 -->
                                <div>
                                    <label for="date" class="block text-sm font-medium text-gray-700">日付 *</label>
                                    <input type="date" name="date" id="date"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required>
                                </div>

                                <!-- 開始時間 -->
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">開始時間 *</label>
                                    <input type="time" name="start_time" id="start_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>

                                <!-- 終了時間 -->
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700">終了時間 *</label>
                                    <input type="time" name="end_time" id="end_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>

                                <button type="button" onclick="searchAvailableUsers()" 
                                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    対応可能なメンバーを検索
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 利用可能メンバー表示・選択 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">2. メンバー選択</h3>
                        
                        <div id="availableUsersSection" class="hidden">
                            <div id="availableUsersList"></div>
                            
                            <form method="POST" action="{{ route('admin.shifts.store', $group) }}" id="createShiftForm" class="mt-4">
                                @csrf
                                <input type="hidden" name="date" id="selected_date">
                                <input type="hidden" name="start_time" id="selected_start_time">
                                <input type="hidden" name="end_time" id="selected_end_time">
                                <input type="hidden" name="user_id" id="selected_user_id">
                                
                                <button type="submit" id="createShiftButton" disabled
                                        class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                                    シフトを作成
                                </button>
                            </form>
                        </div>
                        
                        <div id="noSearchResults" class="text-center py-8 text-gray-500">
                            左のフォームで時間を設定して検索してください
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedUserId = null;

        function searchAvailableUsers() {
            const date = document.getElementById('date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (!date || !startTime || !endTime) {
                alert('すべての項目を入力してください');
                return;
            }

            if (startTime >= endTime) {
                alert('終了時間は開始時間より後に設定してください');
                return;
            }

            // ローディング表示
            document.getElementById('availableUsersList').innerHTML = '<div class="text-center py-4">検索中...</div>';
            document.getElementById('availableUsersSection').classList.remove('hidden');
            document.getElementById('noSearchResults').classList.add('hidden');

            // Ajax検索
            fetch(`{{ route('admin.search-users', $group) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    date: date,
                    start_time: startTime,
                    end_time: endTime
                })
            })
            .then(response => response.json())
            .then(data => {
                displayAvailableUsers(data.availableUsers, date, startTime, endTime);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('availableUsersList').innerHTML = '<div class="text-red-500 text-center py-4">検索中にエラーが発生しました</div>';
            });
        }

        function displayAvailableUsers(users, date, startTime, endTime) {
            const container = document.getElementById('availableUsersList');
            
            if (users.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="mt-2 text-lg">対応可能なメンバーがいません</p>
                        <p class="text-sm">別の時間帯を試してください</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="space-y-3">';
            users.forEach(user => {
                html += `
                    <div class="border rounded-lg p-3 cursor-pointer hover:bg-blue-50 transition-colors user-option" 
                         data-user-id="${user.id}" onclick="selectUser(${user.id}, '${user.name}')">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">${user.name}</div>
                                <div class="text-sm text-gray-600">
                                    対応可能: ${user.available_start_time} - ${user.available_end_time}
                                </div>
                                ${user.comment ? `<div class="text-sm text-gray-500 mt-1">${user.comment}</div>` : ''}
                            </div>
                            <div class="ml-2">
                                <input type="radio" name="selected_user" value="${user.id}" class="h-4 w-4 text-blue-600">
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;

            // フォームにデータを設定
            document.getElementById('selected_date').value = date;
            document.getElementById('selected_start_time').value = startTime;
            document.getElementById('selected_end_time').value = endTime;
        }

        function selectUser(userId, userName) {
            // 他の選択をクリア
            document.querySelectorAll('.user-option').forEach(el => {
                el.classList.remove('bg-blue-100', 'border-blue-500');
                el.classList.add('hover:bg-blue-50');
            });
            
            // 選択されたユーザーをハイライト
            const selectedElement = document.querySelector(`[data-user-id="${userId}"]`);
            selectedElement.classList.add('bg-blue-100', 'border-blue-500');
            selectedElement.classList.remove('hover:bg-blue-50');
            
            // ラジオボタンを選択
            document.querySelector(`input[value="${userId}"]`).checked = true;
            
            // フォームにユーザーIDを設定
            document.getElementById('selected_user_id').value = userId;
            selectedUserId = userId;
            
            // 作成ボタンを有効化
            document.getElementById('createShiftButton').disabled = false;
        }

        // 時間バリデーション
        document.getElementById('start_time').addEventListener('change', function() {
            const endTimeInput = document.getElementById('end_time');
            endTimeInput.min = this.value;
        });

        document.getElementById('end_time').addEventListener('change', function() {
            const startTimeInput = document.getElementById('start_time');
            if (this.value <= startTimeInput.value) {
                alert('終了時間は開始時間より後に設定してください');
                this.value = '';
            }
        });
    </script>
</x-app-layout>