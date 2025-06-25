<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                シフト選考・決定 - {{ $shiftRequest->title }}
            </h2>
            <a href="{{ route('shift-requests.show', [$group, $shiftRequest]) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- 募集情報 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">募集情報</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <strong>募集時間範囲:</strong><br>
                            {{ $shiftRequest->start_time->format('Y/m/d H:i') }} - {{ $shiftRequest->end_time->format('Y/m/d H:i') }}
                        </div>
                        <div>
                            <strong>必要人数:</strong> {{ $shiftRequest->requested_people }}人
                        </div>
                        <div>
                            <strong>応募者数:</strong> {{ $submissions->count() }}人
                        </div>
                    </div>
                </div>
            </div>

            @if($submissions->count() > 0)
                <!-- 選考フォーム -->
                <form method="POST" action="{{ route('shift-requests.process-assignment', [$group, $shiftRequest]) }}">
                    @csrf
                    
                    <!-- 実際のシフト時間設定 -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-4">実際のシフト時間を設定</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="actual_start_time" class="block text-sm font-medium text-gray-700">実際の開始時間</label>
                                    <input type="datetime-local" name="actual_start_time" id="actual_start_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           value="{{ old('actual_start_time', $shiftRequest->start_time->format('Y-m-d\TH:i')) }}" required>
                                    @error('actual_start_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="actual_end_time" class="block text-sm font-medium text-gray-700">実際の終了時間</label>
                                    <input type="datetime-local" name="actual_end_time" id="actual_end_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           value="{{ old('actual_end_time', $shiftRequest->end_time->format('Y-m-d\TH:i')) }}" required>
                                    @error('actual_end_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 応募者選択 -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-4">応募者選択（{{ $shiftRequest->requested_people }}人まで選択）</h4>
                            
                            <div class="space-y-4">
                                @foreach($submissions as $submission)
                                    <div class="border rounded-lg p-4">
                                        <label class="flex items-start space-x-3 cursor-pointer">
                                            <input type="checkbox" name="selected_submissions[]" value="{{ $submission->id }}"
                                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                   {{ collect(old('selected_submissions', []))->contains($submission->id) ? 'checked' : '' }}>
                                            <div class="flex-1">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h5 class="font-medium text-lg">{{ $submission->user->name }}</h5>
                                                        <p class="text-sm text-gray-600">
                                                            <strong>対応可能時間:</strong> 
                                                            {{ $submission->start_time->format('Y/m/d H:i') }} - {{ $submission->end_time->format('Y/m/d H:i') }}
                                                        </p>
                                                        @if($submission->comment)
                                                            <p class="text-sm text-gray-600 mt-1">
                                                                <strong>コメント:</strong> {{ $submission->comment }}
                                                            </p>
                                                        @endif
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            応募日時: {{ $submission->created_at->format('Y/m/d H:i') }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm text-gray-500 mb-1">対応可能時間幅</div>
                                                        <div class="text-lg font-semibold">
                                                            {{ $submission->start_time->diffInHours($submission->end_time) }}時間
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            @error('selected_submissions')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-6 flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    <span id="selected-count">0</span> / {{ $shiftRequest->requested_people }} 人選択中
                                </div>
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    シフトを決定する
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- 重複警告表示エリア -->
                <div id="conflict-warning" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4" style="display: none;">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>時間重複の警告:</strong> 選択されたユーザーが同じ時間帯に他のシフトを持っている可能性があります。
                            </p>
                            <ul id="conflict-list" class="mt-2 text-sm text-yellow-700 list-disc list-inside"></ul>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const checkboxes = document.querySelectorAll('input[name="selected_submissions[]"]');
                        const countDisplay = document.getElementById('selected-count');
                        const maxSelection = {{ $shiftRequest->requested_people }};
                        const startTimeInput = document.getElementById('actual_start_time');
                        const endTimeInput = document.getElementById('actual_end_time');
                        const conflictWarning = document.getElementById('conflict-warning');
                        const conflictList = document.getElementById('conflict-list');

                        // ユーザーの既存シフト情報（サーバーから渡す）
                        const userShifts = @json($submissions->map(function($sub) {
                            return [
                                'user_id' => $sub->user_id,
                                'user_name' => $sub->user->name,
                                'submission_id' => $sub->id,
                                'existing_shifts' => $sub->user->shifts()->select('start_time', 'end_time')->get()
                            ];
                        }));

                        function checkTimeConflicts() {
                            const startTime = new Date(startTimeInput.value);
                            const endTime = new Date(endTimeInput.value);
                            const selectedSubmissions = Array.from(document.querySelectorAll('input[name="selected_submissions[]"]:checked')).map(cb => parseInt(cb.value));
                            
                            const conflicts = [];
                            
                            userShifts.forEach(userData => {
                                if (selectedSubmissions.includes(userData.submission_id)) {
                                    userData.existing_shifts.forEach(shift => {
                                        const shiftStart = new Date(shift.start_time);
                                        const shiftEnd = new Date(shift.end_time);
                                        
                                        // 時間重複チェック
                                        if ((startTime < shiftEnd && endTime > shiftStart)) {
                                            conflicts.push(`${userData.user_name}さん: ${shiftStart.toLocaleString()} - ${shiftEnd.toLocaleString()}`);
                                        }
                                    });
                                }
                            });

                            if (conflicts.length > 0) {
                                conflictList.innerHTML = conflicts.map(conflict => `<li>${conflict}</li>`).join('');
                                conflictWarning.style.display = 'block';
                            } else {
                                conflictWarning.style.display = 'none';
                            }
                        }

                        function updateCount() {
                            const checkedCount = document.querySelectorAll('input[name="selected_submissions[]"]:checked').length;
                            countDisplay.textContent = checkedCount;
                            
                            // 最大選択数に達したら他のチェックボックスを無効化
                            if (checkedCount >= maxSelection) {
                                checkboxes.forEach(checkbox => {
                                    if (!checkbox.checked) {
                                        checkbox.disabled = true;
                                    }
                                });
                            } else {
                                checkboxes.forEach(checkbox => {
                                    checkbox.disabled = false;
                                });
                            }

                            // 重複チェック
                            checkTimeConflicts();
                        }

                        checkboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', updateCount);
                        });

                        // 時間変更時にも重複チェック
                        startTimeInput.addEventListener('change', checkTimeConflicts);
                        endTimeInput.addEventListener('change', checkTimeConflicts);

                        // 初期状態の更新
                        updateCount();
                    });
                </script>

            @else
                <!-- 応募者がいない場合 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-500">
                            <p class="text-lg mb-2">応募者がいません</p>
                            <p>まだ誰も応募していません。応募期限まで待つか、募集内容を見直してください。</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>