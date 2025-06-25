<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                登録済み可用性 - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('availability.create', $group) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    新規登録
                </a>
                <a href="{{ route('groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    戻る
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($availabilities->count() > 0)
                        <div class="space-y-4">
                            @foreach($availabilities as $availability)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-4 mb-2">
                                                <div class="font-medium text-lg">
                                                    {{ $availability->date->format('Y年m月d日') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ({{ $availability->date->format('D') }})
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($availability->status === 'active') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    @if($availability->status === 'active') 有効
                                                    @else 無効 @endif
                                                </span>
                                            </div>
                                            
                                            <div class="text-sm text-gray-600 mb-2">
                                                <strong>時間:</strong> 
                                                {{ \Carbon\Carbon::parse($availability->available_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($availability->available_end_time)->format('H:i') }}
                                                <span class="ml-2 text-gray-500">
                                                    （{{ \Carbon\Carbon::parse($availability->available_start_time)->diffInHours(\Carbon\Carbon::parse($availability->available_end_time)) }}時間）
                                                </span>
                                            </div>
                                            
                                            @if($availability->comment)
                                                <div class="text-sm text-gray-500">
                                                    <strong>コメント:</strong> {{ $availability->comment }}
                                                </div>
                                            @endif
                                            
                                            <div class="text-xs text-gray-400 mt-2">
                                                登録日時: {{ $availability->created_at->format('Y/m/d H:i') }}
                                                @if($availability->updated_at != $availability->created_at)
                                                    | 更新日時: {{ $availability->updated_at->format('Y/m/d H:i') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col space-y-2">
                                            <a href="{{ route('availability.create', $group) }}?edit={{ $availability->id }}" 
                                               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-sm text-center">
                                                編集
                                            </a>
                                            <form method="POST" action="{{ route('availability.destroy', [$group, $availability]) }}" 
                                                  class="inline" onsubmit="return confirm('この可用性を削除しますか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 text-sm text-gray-500 text-center">
                            合計 {{ $availabilities->count() }} 件の可用性が登録されています
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">可用性が登録されていません</h3>
                            <p class="mt-1 text-sm text-gray-500">働ける日程を登録して、シフトの調整に役立てましょう</p>
                            <div class="mt-6">
                                <a href="{{ route('availability.create', $group) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    最初の可用性を登録
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>