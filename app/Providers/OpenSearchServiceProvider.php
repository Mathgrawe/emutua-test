<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpenSearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts([[
                    'host'   => config('opensearch.host'),
                    'port'   => config('opensearch.port'),
                    'scheme' => config('opensearch.scheme', 'http'),
                ]])
                ->setLogger($app->make('log'))
                ->build();
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