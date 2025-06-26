<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Heroku用のHTTPS設定
        if (app()->environment('production')) {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // セッションディレクトリの確実な作成
        $sessionPath = storage_path('framework/sessions');
        if (!file_exists($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }
    }
}
