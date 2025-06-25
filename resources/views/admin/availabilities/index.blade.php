<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                メンバー可用性一覧 - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.shifts.create', $group) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    シフト作成
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
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($availabilities->count() > 0)
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">今後の可用性一覧</h3>
                            <p class="text-sm text-gray-600">メンバーが登録した勤務可能時間を確認できます</p>
                        </div>
                        
                        <div class="space-y-6">
                            @foreach($availabilities as $date => $dayAvailabilities)
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 border-b">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($date)->format('Y年m月d日 (D)') }}
                                            </h4>
                                            <div class="text-sm text-gray-600">
                                                {{ $dayAvailabilities->count() }}人が勤務可能
                                            </div>
                                        </div>
                                    </div>
                                    <div class="divide-y">
                                        @foreach($dayAvailabilities as $availability)
                                            <div class="p-4 hover:bg-gray-50">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                            <span class="text-white font-medium">
                                                                {{ substr($availability->user->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="font-medium text-lg">{{ $availability->user->name }}</div>
                                                            <div class="text-sm text-gray-600">
                                                                <strong>時間:</strong> 
                                                                {{ \Carbon\Carbon::parse($availability->available_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($availability->available_end_time)->format('H:i') }}
                                                                <span class="ml-2 text-gray-500">
                                                                    （{{ \Carbon\Carbon::parse($availability->available_start_time)->diffInHours(\Carbon\Carbon::parse($availability->available_end_time)) }}時間）
                                                                </span>
                                                            </div>
                                                            @if($availability->comment)
                                                                <div class="text-sm text-gray-500 mt-1">
                                                                    <strong>コメント:</strong> {{ $availability->comment }}
                                                                </div>
                                                            @endif
                                                            <div class="text-xs text-gray-400 mt-1">
                                                                登録日時: {{ $availability->created_at->format('Y/m/d H:i') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <button onclick="createShiftForUser('{{ $date }}', '{{ $availability->available_start_time }}', '{{ $availability->available_end_time }}', {{ $availability->user->id }}, '{{ $availability->user->name }}')"
                                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-sm">
                                                            シフト作成
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">可用性が登録されていません</h3>
                            <p class="mt-1 text-sm text-gray-500">メンバーに勤務可能時間の登録を依頼してください</p>
                            <div class="mt-6">
                                <a href="{{ route('groups.show', $group) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    グループページに戻る
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- クイックシフト作成モーダル -->
    <div id="quickShiftModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">クイックシフト作成</h3>
                <form method="POST" action="{{ route('admin.shifts.store', $group) }}" id="quickShiftForm">
                    @csrf
                    <input type="hidden" name="date" id="modal_date">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">担当者</label>
                        <div id="modal_user_name" class="text-lg font-medium text-gray-900"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">日付</label>
                        <div id="modal_date_display" class="text-lg font-medium text-gray-900"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="modal_start_time" class="block text-sm font-medium text-gray-700">開始時間</label>
                            <input type="time" name="start_time" id="modal_start_time" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="modal_end_time" class="block text-sm font-medium text-gray-700">終了時間</label>
                            <input type="time" name="end_time" id="modal_end_time" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeQuickShiftModal()" 
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            キャンセル
                        </button>
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            作成
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function createShiftForUser(date, startTime, endTime, userId, userName) {
            document.getElementById('modal_date').value = date;
            document.getElementById('modal_user_id').value = userId;
            document.getElementById('modal_user_name').textContent = userName;
            document.getElementById('modal_date_display').textContent = new Date(date).toLocaleDateString('ja-JP', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'short'
            });
            document.getElementById('modal_start_time').value = startTime;
            document.getElementById('modal_end_time').value = endTime;
            
            document.getElementById('quickShiftModal').classList.remove('hidden');
        }

        function closeQuickShiftModal() {
            document.getElementById('quickShiftModal').classList.add('hidden');
        }

        // モーダル外クリックで閉じる
        document.getElementById('quickShiftModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickShiftModal();
            }
        });
    </script>
</x-app-layout>