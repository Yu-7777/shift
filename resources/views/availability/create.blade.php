<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                可用性登録 - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('availability.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    一覧を見る
                </a>
                <a href="{{ route('groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- 説明 -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-800 mb-2">可用性登録について</h3>
                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li>働ける日とその時間帯を事前に登録してください</li>
                    <li>管理者がシフトを作成する際の参考になります</li>
                    <li>登録後もいつでも変更・削除が可能です</li>
                    <li>実際のシフト時間は管理者が決定し、後日通知されます</li>
                </ul>
            </div>

            <!-- 可用性登録フォーム -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">新しい可用性を登録</h3>
                    
                    <form method="POST" action="{{ route('availability.store', $group) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- 日付 -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">日付 *</label>
                                <input type="date" name="date" id="date"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('date') }}" 
                                       min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                       required>
                                @error('date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 開始時間 -->
                            <div>
                                <label for="available_start_time" class="block text-sm font-medium text-gray-700">開始時間 *</label>
                                <input type="time" name="available_start_time" id="available_start_time"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('available_start_time') }}" required>
                                @error('available_start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 終了時間 -->
                            <div>
                                <label for="available_end_time" class="block text-sm font-medium text-gray-700">終了時間 *</label>
                                <input type="time" name="available_end_time" id="available_end_time"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('available_end_time') }}" required>
                                @error('available_end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- コメント -->
                        <div class="mb-4">
                            <label for="comment" class="block text-sm font-medium text-gray-700">コメント（任意）</label>
                            <textarea name="comment" id="comment" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="特記事項があれば入力してください（例：早めに帰りたい、遅刻の可能性など）">{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                登録する
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 既存の可用性一覧 -->
            @if($existingAvailabilities->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">登録済みの可用性（今後30日間）</h3>
                        
                        <div class="space-y-3">
                            @foreach($existingAvailabilities as $availability)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium text-lg">
                                                {{ $availability->date->format('Y年m月d日 (D)') }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ $availability->available_start_time }} - {{ $availability->available_end_time }}
                                                （{{ \Carbon\Carbon::parse($availability->available_start_time)->diffInHours(\Carbon\Carbon::parse($availability->available_end_time)) }}時間）
                                            </div>
                                            @if($availability->comment)
                                                <div class="text-sm text-gray-500 mt-1">
                                                    {{ $availability->comment }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="editAvailability({{ $availability->id }}, '{{ $availability->date->format('Y-m-d') }}', '{{ $availability->available_start_time }}', '{{ $availability->available_end_time }}', '{{ addslashes($availability->comment) }}')"
                                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                編集
                                            </button>
                                            <form method="POST" action="{{ route('availability.destroy', [$group, $availability]) }}" class="inline"
                                                  onsubmit="return confirm('この可用性を削除しますか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function editAvailability(id, date, startTime, endTime, comment) {
            // フォームに既存データを設定
            document.getElementById('date').value = date;
            document.getElementById('available_start_time').value = startTime;
            document.getElementById('available_end_time').value = endTime;
            document.getElementById('comment').value = comment;
            
            // フォームにスクロール
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        }

        // 時間バリデーション
        document.getElementById('available_start_time').addEventListener('change', function() {
            const endTimeInput = document.getElementById('available_end_time');
            endTimeInput.min = this.value;
        });

        document.getElementById('available_end_time').addEventListener('change', function() {
            const startTimeInput = document.getElementById('available_start_time');
            if (this.value <= startTimeInput.value) {
                alert('終了時間は開始時間より後に設定してください');
                this.value = '';
            }
        });
    </script>
</x-app-layout>