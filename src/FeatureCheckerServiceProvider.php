<?php

namespace AgenterLab\FeatureChecker;

use Illuminate\Support\ServiceProvider;

class FeatureCheckerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/saas.php', 'saas');

        $this->app->singleton('saas', function ($app) {
            return new Saas(config('saas.storage'));
        });

        $this->app->singleton('saas.request', function ($app) {
            return new Request(
                config('saas.key'),
                config('saas.token_name'),
                config('saas.request_restrict')
            );
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
    }
}
