<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Dive uploads from the apps carry megabytes of profile samples;
        // trimming and null-converting every one of them only burns memory
        // (2026-10-25: part of the 128 MB failures on Liberty uploads).
        $isDiveUpload = fn ($request) => $request->isMethod('POST') && $request->is('api/dives');
        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen($isDiveUpload);
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen($isDiveUpload);

        //
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
            \URL::forceRootUrl(config('app.url'));
        }
    }
}
