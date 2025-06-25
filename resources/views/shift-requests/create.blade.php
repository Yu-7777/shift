<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                シフト募集作成 - {{ $group->name }}
            </h2>
            <a href="{{ route('groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('shift-requests.store', $group) }}">
                        @csrf

                        <!-- 募集タイトル -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">募集タイトル</label>
                            <input type="text" name="title" id="title" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 募集詳細 -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">募集詳細</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- 開始時間 -->
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">シフト開始時間</label>
                                <input type="datetime-local" name="start_time" id="start_time"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 終了時間 -->
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">シフト終了時間</label>
                                <input type="datetime-local" name="end_time" id="end_time"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- 必要人数 -->
                            <div>
                                <label for="requested_people" class="block text-sm font-medium text-gray-700">必要人数</label>
                                <input type="number" name="requested_people" id="requested_people" min="1"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('requested_people', 1) }}" required>
                                @error('requested_people')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 応募締切 -->
                            <div>
                                <label for="application_deadline" class="block text-sm font-medium text-gray-700">応募締切</label>
                                <input type="datetime-local" name="application_deadline" id="application_deadline"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       value="{{ old('application_deadline') }}" required>
                                @error('application_deadline')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                募集を作成
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>