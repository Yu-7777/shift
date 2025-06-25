<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $shiftRequest->title ?? 'シフト募集詳細' }} - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                @if(auth()->user()->groupMembers()->where('group_id', $group->id)->where('role_id', App\Models\GroupMember::ROLE_ADMIN)->exists())
                    @if($shiftRequest->status === 'closed')
                        <a href="{{ route('shift-requests.assign', [$group, $shiftRequest]) }}" 
                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            選考・決定
                        </a>
                    @endif
                @endif
                <a href="{{ route('groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- 募集詳細 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($shiftRequest->status === 'open') bg-green-100 text-green-800
                            @elseif($shiftRequest->status === 'closed') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            @if($shiftRequest->status === 'open') 応募受付中
                            @elseif($shiftRequest->status === 'closed') 応募締切
                            @else 決定済み @endif
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold mb-2">{{ $shiftRequest->title }}</h3>
                    
                    @if($shiftRequest->description)
                        <div class="mb-4">
                            <p class="text-gray-700">{{ $shiftRequest->description }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <strong>シフト時間:</strong><br>
                            {{ $shiftRequest->start_time ? $shiftRequest->start_time->format('Y/m/d H:i') : '未設定' }} - 
                            {{ $shiftRequest->end_time ? $shiftRequest->end_time->format('Y/m/d H:i') : '未設定' }}
                        </div>
                        <div>
                            <strong>必要人数:</strong> {{ $shiftRequest->requested_people }}人
                        </div>
                        <div>
                            <strong>応募締切:</strong><br>
                            {{ $shiftRequest->application_deadline ? $shiftRequest->application_deadline->format('Y/m/d H:i') : '未設定' }}
                        </div>
                        <div>
                            <strong>作成者:</strong> {{ $shiftRequest->creator->name ?? '不明' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 応募セクション -->
            @if($shiftRequest->canApply())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold mb-4">応募する</h4>
                        
                        @if($userSubmission)
                            <!-- 既に応募済み -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h5 class="font-medium text-blue-800 mb-2">あなたの応募内容</h5>
                                <div class="text-sm text-blue-700">
                                    <p><strong>対応可能時間:</strong> 
                                       {{ $userSubmission->start_time->format('Y/m/d H:i') }} - {{ $userSubmission->end_time->format('Y/m/d H:i') }}</p>
                                    @if($userSubmission->comment)
                                        <p><strong>コメント:</strong> {{ $userSubmission->comment }}</p>
                                    @endif
                                    <p><strong>ステータス:</strong> 
                                        @if($userSubmission->status === 'pending') 審査中
                                        @elseif($userSubmission->status === 'selected') 選択済み
                                        @else 却下 @endif
                                    </p>
                                </div>
                                
                                @if($userSubmission->status === 'pending')
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('shift-submissions.edit', [$group, $shiftRequest, $userSubmission]) }}" 
                                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                            編集
                                        </a>
                                        <form method="POST" action="{{ route('shift-submissions.destroy', [$group, $shiftRequest, $userSubmission]) }}" 
                                              class="inline" onsubmit="return confirm('応募を取り消しますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                取り消し
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- 応募フォーム -->
                            <form method="POST" action="{{ route('shift-submissions.store', [$group, $shiftRequest]) }}">
                                @csrf
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="start_time" class="block text-sm font-medium text-gray-700">対応可能開始時間</label>
                                        <input type="datetime-local" name="start_time" id="start_time"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               value="{{ old('start_time') }}" required>
                                        @error('start_time')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="end_time" class="block text-sm font-medium text-gray-700">対応可能終了時間</label>
                                        <input type="datetime-local" name="end_time" id="end_time"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               value="{{ old('end_time') }}" required>
                                        @error('end_time')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="comment" class="block text-sm font-medium text-gray-700">コメント（任意）</label>
                                    <textarea name="comment" id="comment" rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                              placeholder="何かあればお書きください">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    応募する
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @elseif($shiftRequest->status === 'open')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800">応募期限が過ぎています。</p>
                </div>
            @endif

            <!-- 応募者一覧（管理者のみ） -->
            @if(auth()->user()->groupMembers()->where('group_id', $group->id)->where('role_id', App\Models\GroupMember::ROLE_ADMIN)->exists())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold mb-4">応募者一覧 ({{ $shiftRequest->submissions->count() }}人)</h4>
                        
                        @if($shiftRequest->submissions->count() > 0)
                            <div class="space-y-4">
                                @foreach($shiftRequest->submissions as $submission)
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-medium">{{ $submission->user->name }}</h5>
                                                <p class="text-sm text-gray-600">
                                                    対応可能時間: {{ $submission->start_time->format('Y/m/d H:i') }} - {{ $submission->end_time->format('Y/m/d H:i') }}
                                                </p>
                                                @if($submission->comment)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $submission->comment }}</p>
                                                @endif
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($submission->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($submission->status === 'selected') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                @if($submission->status === 'pending') 審査中
                                                @elseif($submission->status === 'selected') 選択済み
                                                @else 却下 @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">まだ応募者がいません。</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>