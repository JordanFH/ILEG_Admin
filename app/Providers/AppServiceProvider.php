<?php

namespace App\Providers;

use App\Models\Producto;
use App\Observers\ProductObserver;
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
        Producto::observe(ProductObserver::class);
        if (config('app.env') === 'production')
        {
            URL::forceScheme('https');
        }
    }
}
