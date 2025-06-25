<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $group->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- 左側: アクションメニュー -->
                <div class="lg:col-span-3">
                    <div class="space-y-4">
                        <!-- シフト入力 -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">シフト管理</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                @if(auth()->user()->groupMembers()->where('group_id', $group->id)->where('role_id', App\Models\GroupMember::ROLE_ADMIN)->exists())
                                    <!-- 管理者用ボタン -->
                                    <a href="{{ route('admin.shifts.create', $group) }}" class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span class="text-sm font-medium">シフト作成</span>
                                    </a>
                                    <a href="{{ route('admin.availabilities.index', $group) }}" class="w-full flex items-center justify-center px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        <span class="text-sm font-medium">可用性確認</span>
                                    </a>
                                @else
                                    <!-- 従業員用ボタン -->
                                    <a href="{{ route('availability.create', $group) }}" class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">可用性登録</span>
                                    </a>
                                @endif
                                <a href="{{ route('availability.index', $group) }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700">可用性一覧</span>
                                </a>
                            </div>
                        </div>

                        <!-- チャット -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">コミュニケーション</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <form action="{{ route('chats.create-group', $group) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">グループチャット作成</span>
                                    </button>
                                </form>
                                <a href="{{ route('chats.index') }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700">チャット一覧</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 中央: シフトカレンダー -->
                <div class="lg:col-span-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">シフトカレンダー</h3>
                                <p class="text-sm text-gray-600">{{ $calendar['displayMonth'] }}</p>
                            </div>
                        </div>
                        <div class="p-6">
                            
                            <!-- カレンダーヘッダー -->
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                @foreach(\App\Helpers\CalendarHelper::getDayHeaders() as $day)
                                    <div class="text-center text-sm font-semibold text-gray-600 py-2">
                                        {{ $day }}
                                    </div>
                                @endforeach
                            </div>

                            <!-- カレンダーボディ -->
                            @foreach($calendar['weeks'] as $week)
                                <div class="grid grid-cols-7 gap-1 mb-1">
                                    @foreach($week as $date)
                                        @php
                                            $dayShifts = \App\Helpers\CalendarHelper::getShiftsForDate($shifts, $date);
                                        @endphp
                                        
                                        <div class="{{ \App\Helpers\CalendarHelper::getDateStyleClass($date, $calendar['currentDate']) }}">
                                            <div class="{{ \App\Helpers\CalendarHelper::getDateTextStyleClass($date, $calendar['currentDate']) }}">
                                                {{ $date->format('j') }}
                                            </div>
                                            
                                            @if($dayShifts->count() > 0)
                                                <div class="mt-1">
                                                    @foreach($dayShifts as $shift)
                                                        <div class="text-xs bg-blue-100 text-blue-800 px-1 py-0.5 rounded mb-1">
                                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                                            <div class="text-xs text-gray-600">
                                                                {{ $shift->user->name ?? '未割当' }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- 右側: メンバーリスト -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-900">メンバー</h3>
                        </div>
                        <div class="p-4">
                            @if($members->count() > 0)
                                <div class="space-y-3">
                                    @foreach($members as $member)
                                        <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-white text-sm font-medium">
                                                    {{ substr($member->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $member->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if($member->pivot->role_id == \App\Models\GroupMember::ROLE_ADMIN)
                                                        管理者
                                                    @elseif($member->pivot->role_id == \App\Models\GroupMember::ROLE_MEMBER)
                                                        メンバー
                                                    @else
                                                        不明
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">メンバーがいません</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>