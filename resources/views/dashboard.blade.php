<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ダッシュボード
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- 左側: ログイン成功メッセージとクイックアクション -->
                <div class="lg:col-span-8">
                    <div class="space-y-6">
                        <!-- ウェルカムメッセージ -->
                        <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
                            <h2 class="text-xl font-bold mb-2">ログインが完了しました</h2>
                            <p class="text-green-100">シフト管理システムへようこそ。各機能をご利用ください。</p>
                        </div>

                        <!-- システム概要 -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">システム概要</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">グループ管理</h4>
                                            <p class="text-sm text-gray-500">チームごとのシフト管理</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-4 bg-green-50 rounded-lg">
                                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">シフト管理</h4>
                                            <p class="text-sm text-gray-500">可用性の登録と確認</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-4 bg-purple-50 rounded-lg">
                                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">チャット機能</h4>
                                            <p class="text-sm text-gray-500">チーム内コミュニケーション</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-4 bg-yellow-50 rounded-lg">
                                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">プロフィール管理</h4>
                                            <p class="text-sm text-gray-500">個人情報の更新</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 右側: ナビゲーション -->
                <div class="lg:col-span-4">
                    <div class="space-y-4">
                        <!-- クイックナビゲーション -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">クイックナビゲーション</h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <a href="{{ route('home') }}" class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-sm font-medium">ホーム</span>
                                </a>
                                <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700">プロフィール</span>
                                </a>
                            </div>
                        </div>

                        <!-- ヘルプ・サポート -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">ヘルプ・サポート</h3>
                            </div>
                            <div class="p-4">
                                <div class="text-center py-4">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">
                                        ご不明な点がございましたら<br>
                                        サポートまでお問い合わせください
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
