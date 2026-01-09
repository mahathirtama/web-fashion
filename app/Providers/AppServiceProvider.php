<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;   // <--- Wajib ada (1)
use Illuminate\Support\Facades\Config;

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
    // HAPUS semua 'if'
    // Langsung paksa gunakan HTTPS
    \Illuminate\Support\Facades\URL::forceScheme('http');

    // Langsung paksa gunakan Domain dari .env
    // Pastikan di .env APP_URL sudah benar
    \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
}
}
