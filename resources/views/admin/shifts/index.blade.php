<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                シフト管理 - {{ $group->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.shifts.create', $group) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    新規作成
                </a>
                <a href="{{ route('admin.availabilities.index', $group) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    可用性一覧
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
                    @if($shifts->count() > 0)
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">今後のシフト一覧</h3>
                            <p class="text-sm text-gray-600">合計 {{ $shifts->count() }} 件のシフトが登録されています</p>
                        </div>
                        
                        <div class="space-y-4">
                            @foreach($shifts->groupBy(function($shift) { return $shift->start_time->format('Y-m-d'); }) as $date => $dayShifts)
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-2 border-b">
                                        <h4 class="font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($date)->format('Y年m月d日 (D)') }}
                                        </h4>
                                    </div>
                                    <div class="divide-y">
                                        @foreach($dayShifts as $shift)
                                            <div class="p-4 hover:bg-gray-50">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-4 mb-2">
                                                            <div class="font-medium text-lg">
                                                                {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                （{{ $shift->start_time->diffInHours($shift->end_time) }}時間）
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                                <span class="text-white text-sm font-medium">
                                                                    {{ substr($shift->user->name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium">{{ $shift->user->name }}</div>
                                                                <div class="text-sm text-gray-500">
                                                                    作成日時: {{ $shift->created_at->format('Y/m/d H:i') }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex flex-col space-y-2">
                                                        <form method="POST" action="{{ route('admin.shifts.destroy', [$group, $shift]) }}" 
                                                              class="inline" onsubmit="return confirm('このシフトを削除しますか？')">
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
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-4m-4 0h8m-4-4v4m-4-4V9a4 4 0 014-4v4"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">シフトが作成されていません</h3>
                            <p class="mt-1 text-sm text-gray-500">メンバーの可用性を確認して、最初のシフトを作成しましょう</p>
                            <div class="mt-6 flex justify-center space-x-3">
                                <a href="{{ route('admin.availabilities.index', $group) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    可用性を確認
                                </a>
                                <a href="{{ route('admin.shifts.create', $group) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    シフトを作成
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>