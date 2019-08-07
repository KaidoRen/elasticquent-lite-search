<?php

namespace KaidoRen\ELSearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

final class ELSearchServiceProvder extends ServiceProvider
{
    /**
     * Register the application services
     * 
     * @return void
     */
    public function register(): void
    {
        $clientBuilder = ClientBuilder::create()
            ->setHosts(config('elsearch.elasticsearch.hosts'))
            ->build();
        
        $this->app->bind(Client::class, function() use ($clientBuilder) {
            return $clientBuilder;
        });

        $this->app->singleton('elasticsearch', function() use ($clientBuilder) {
            return $clientBuilder;
        });
    }

    /**
     * Bootstrap the application services
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/elsearch.php' => config_path('elsearch.php')
        ]);
    }
}