<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Set locale Carbon ke Bahasa Indonesia
        Carbon::setLocale(config('app.locale', 'id'));

        // Force HTTPS di production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Fix: pastikan auth providers pakai class yang benar
        // (workaround untuk Railpack config cache serialization bug)
        config([
            'auth.providers.users.model'  => \App\Models\User::class,
            'auth.providers.siswas.model' => \App\Models\Siswa::class,
        ]);
    }
}
