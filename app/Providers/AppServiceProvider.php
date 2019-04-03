<?php

namespace App\Providers;

use App\Models\Api;
use App\Observers\ApiObserver;
use App\Services\HttpService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Api::observe(ApiObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('user',function(){
            return app(UserService::class);
        });
        $this->app->bind('http',function(){
            return app(HttpService::class);
        });
    }
}
