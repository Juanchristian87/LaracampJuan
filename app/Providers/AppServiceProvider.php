<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (env(key: 'APP_ENV') !== 'local') {
            URL::forceScheme(scheme: 'http');
        }
        //check that app is local
        // if ($this->app=='local') {
        //     //if local register your services you require for development
        //     // $this->app->register('Barryvdh\Debugbar\ServiceProvider');
        // } else {
        //     //else register your services you require for production
        //     $this->app['request']->server->set('HTTPS', true);
        // }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
