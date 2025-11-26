<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Comms\CommsService;
use App\Services\Comms\NullCommsService;

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
        $this->app->bind(CommsService::class, NullCommsService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
