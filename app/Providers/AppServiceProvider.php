<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DeliveryTypeServiceInterface;
use App\Services\DeliveryTypeService;
use App\Contracts\ServicePayServiceInterface;
use App\Services\ServicePayService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            DeliveryTypeServiceInterface::class,
            DeliveryTypeService::class
        );

        $this->app->bind(
            ServicePayServiceInterface::class,
            ServicePayService::class
        );
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
