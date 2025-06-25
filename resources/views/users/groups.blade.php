<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}の所属グループ
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('users.show', $user) }}" 
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    ← {{ $user->name }}の詳細に戻る
                </a>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4">
                    @if($groups->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($groups as $group)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $group->name }}</h3>
                                        @if(isset($group->pivot) && $group->pivot->role_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                @if($group->pivot->role_id == 1)
                                                    管理者
                                                @else
                                                    メンバー
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if(isset($group->group_members_count))
                                        <p class="text-sm text-gray-600 mb-3">
                                            メンバー数: {{ $group->group_members_count }}名
                                        </p>
                                    @endif
                                    
                                    @if(isset($group->pivot) && $group->pivot->created_at)
                                        <p class="text-sm text-gray-500 mb-3">
                                            参加日: {{ $group->pivot->created_at->format('Y年m月d日') }}
                                        </p>
                                    @endif
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('groups.show', $group) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            グループを見る
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="max-w-md mx-auto">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">所属グループなし</h3>
                                <p class="text-gray-500">{{ $user->name }}はまだどのグループにも所属していません。</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>