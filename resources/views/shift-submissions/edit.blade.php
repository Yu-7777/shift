<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                応募内容編集 - {{ $shiftRequest->title }}
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
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="font-medium text-blue-800 mb-2">{{ $shiftRequest->title }}</h4>
                        @if($shiftRequest->description)
                            <p class="text-blue-700 mb-2">{{ $shiftRequest->description }}</p>
                        @endif
                        <div class="text-sm text-blue-600">
                            <p><strong>シフト時間:</strong> {{ $shiftRequest->start_time->format('Y/m/d H:i') }} - {{ $shiftRequest->end_time->format('Y/m/d H:i') }}</p>
                            <p><strong>必要人数:</strong> {{ $shiftRequest->requested_people }}人</p>
                            <p><strong>応募締切:</strong> {{ $shiftRequest->application_deadline->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 現在の応募内容 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">現在の応募内容</h3>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="text-sm text-gray-700">
                            <p><strong>現在の対応可能時間:</strong> {{ $shiftSubmission->start_time->format('Y/m/d H:i') }} - {{ $shiftSubmission->end_time->format('Y/m/d H:i') }}</p>
                            @if($shiftSubmission->comment)
                                <p><strong>現在のコメント:</strong> {{ $shiftSubmission->comment }}</p>
                            @else
                                <p><strong>現在のコメント:</strong> なし</p>
                            @endif
                            <p><strong>応募日時:</strong> {{ $shiftSubmission->created_at->format('Y/m/d H:i') }}</p>
                            <p><strong>ステータス:</strong> 
                                @if($shiftSubmission->status === 'pending') 審査中
                                @elseif($shiftSubmission->status === 'selected') 選択済み
                                @else 却下 @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 応募編集フォーム -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">応募内容を編集</h3>
                    
                    <form method="POST" action="{{ route('shift-submissions.update', [$group, $shiftRequest, $shiftSubmission]) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-6">
                            <h4 class="font-medium mb-3">対応可能な時間を修正してください</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                募集時間（{{ $shiftRequest->start_time->format('Y/m/d H:i') }} - {{ $shiftRequest->end_time->format('Y/m/d H:i') }}）の範囲内で、
                                あなたが対応可能な時間を入力してください。
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">対応可能開始時間 *</label>
                                    <input type="datetime-local" name="start_time" id="start_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           value="{{ old('start_time', $shiftSubmission->start_time->format('Y-m-d\TH:i')) }}" 
                                           min="{{ $shiftRequest->start_time->format('Y-m-d\TH:i') }}"
                                           max="{{ $shiftRequest->end_time->format('Y-m-d\TH:i') }}"
                                           required>
                                    @error('start_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700">対応可能終了時間 *</label>
                                    <input type="datetime-local" name="end_time" id="end_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           value="{{ old('end_time', $shiftSubmission->end_time->format('Y-m-d\TH:i')) }}" 
                                           min="{{ $shiftRequest->start_time->format('Y-m-d\TH:i') }}"
                                           max="{{ $shiftRequest->end_time->format('Y-m-d\TH:i') }}"
                                           required>
                                    @error('end_time')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="comment" class="block text-sm font-medium text-gray-700">コメント（任意）</label>
                            <textarea name="comment" id="comment" rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="特に伝えたいことがあればお書きください（経験、意気込み、注意事項など）">{{ old('comment', $shiftSubmission->comment) }}</textarea>
                            @error('comment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">最大1000文字まで入力できます。</p>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <h5 class="font-medium text-yellow-800 mb-2">注意事項</h5>
                            <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                                <li>内容を変更すると募集者に更新通知が送られます</li>
                                <li>応募締切前であれば何度でも編集可能です</li>
                                <li>選択された後は編集できません</li>
                                <li>応募を取り消したい場合は「応募取り消し」ボタンを使用してください</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <!-- 応募取り消しボタン -->
                                <button type="button" 
                                        onclick="if(confirm('応募を取り消しますか？この操作は元に戻せません。')) { document.getElementById('delete-form').submit(); }"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    応募を取り消し
                                </button>
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('shift-requests.show', [$group, $shiftRequest]) }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    キャンセル
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    変更を保存
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- 削除用の隠しフォーム -->
                    <form id="delete-form" method="POST" action="{{ route('shift-submissions.destroy', [$group, $shiftRequest, $shiftSubmission]) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            // 開始時間が変更されたら終了時間の最小値を更新
            startTimeInput.addEventListener('change', function() {
                endTimeInput.min = this.value;
            });

            // 終了時間が変更されたら開始時間の最大値を更新
            endTimeInput.addEventListener('change', function() {
                startTimeInput.max = this.value;
            });
        });
    </script>
</x-app-layout>