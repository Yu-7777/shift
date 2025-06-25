<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                シフト応募 - {{ $shiftRequest->title }}
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

            <!-- 応募フォーム -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">応募内容</h3>
                    
                    <form method="POST" action="{{ route('shift-submissions.store', [$group, $shiftRequest]) }}">
                        @csrf

                        <div class="mb-6">
                            <h4 class="font-medium mb-3">対応可能な時間を入力してください</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                募集時間（{{ $shiftRequest->start_time->format('Y/m/d H:i') }} - {{ $shiftRequest->end_time->format('Y/m/d H:i') }}）の範囲内で、
                                あなたが対応可能な時間を入力してください。
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">対応可能開始時間 *</label>
                                    <input type="datetime-local" name="start_time" id="start_time"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           value="{{ old('start_time') }}" 
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
                                           value="{{ old('end_time') }}" 
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
                                      placeholder="特に伝えたいことがあればお書きください（経験、意気込み、注意事項など）">{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">最大1000文字まで入力できます。</p>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <h5 class="font-medium text-yellow-800 mb-2">注意事項</h5>
                            <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                                <li>応募後は募集者に内容が通知されます</li>
                                <li>応募締切前であれば内容の編集・取り消しが可能です</li>
                                <li>選考結果は募集者により決定されます</li>
                                <li>選択された後はキャンセルできません</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('shift-requests.show', [$group, $shiftRequest]) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                キャンセル
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                応募する
                            </button>
                        </div>
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