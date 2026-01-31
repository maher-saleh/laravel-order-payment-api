<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PaymentGatewayManager;

/**
 * Payment Service Provider.
 * 
 * Registers payment-related services in the container.
 */
class PaymentServiceProvider extends ServiceProvider
{
    
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register PaymentGatewayManager as singleton
        // This ensures the same instance is used throughout the request
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager();
        });
    }



    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}