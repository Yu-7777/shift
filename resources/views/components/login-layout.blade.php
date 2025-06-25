<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-6">
            <!-- ロゴ・タイトル部分 -->
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">シフト管理システム</h2>
                <p class="mt-2 text-sm text-gray-600">{{ $description ?? 'アカウントにログインしてください' }}</p>
            </div>

            <!-- フォーム部分 -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $title ?? 'ログイン' }}</h3>
                </div>
                <div class="p-6">
                    {{ $slot }}
                </div>
            </div>

            <!-- フッターリンク -->
            @if(isset($footerLink) && isset($footerText))
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        {{ $footerText }}
                        <a href="{{ $footerLink }}" class="font-medium text-blue-600 hover:text-blue-500">
                            {{ $footerLinkText }}
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
