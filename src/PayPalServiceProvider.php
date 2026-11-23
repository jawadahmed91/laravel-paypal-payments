<?php

namespace JawadAhmed\LaravelPayPal;

use Illuminate\Support\ServiceProvider;

final class PayPalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/paypal.php', 'paypal');
        $this->app->singleton(PayPalClient::class, fn () => new PayPalClient(
            (string) config('paypal.client_id'),
            (string) config('paypal.client_secret'),
            (string) config('paypal.mode', 'sandbox'),
            config('paypal.webhook_id'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/paypal.php' => config_path('paypal.php')], 'paypal-config');
    }
}
