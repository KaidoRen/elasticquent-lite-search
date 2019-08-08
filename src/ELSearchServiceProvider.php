<?php

namespace KaidoRen\ELSearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use KaidoRen\ELSearch\{Utils\ElasticsearchUtils, Console\Commands\Import};

final class ELSearchServiceProvider extends ServiceProvider
{
    /**
     * Register the application services
     * 
     * @return void
     */
    public function register(): void
    {
        $this->bindingElasticsearchClient($client = ClientBuilder::create()
            ->setHosts(config('elsearch.elasticsearch.hosts'))
            ->build());
        
        $this->bindingElasticsearchUtils($client);
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

        if ($this->app->runningInConsole()) {
            $this->commands([
                Import::class
            ]);
        }
    }

    /**
     * Binding Elasticsearch client into Laravel's service container
     * 
     * @param Client        $client
     */
    protected function bindingElasticsearchClient(Client $client)
    {
        $this->app->bind(Client::class, function() use ($client) {
            return $client;
        });

        $this->app->singleton('elasticsearch', function() use ($client) {
            return $client;
        });
    }

    /**
     * Binding Elasticsearch utils object into Laravel's service container
     * 
     * @param Client        $client
     */
    protected function bindingElasticsearchUtils(Client $client)
    {
        $utils = new ElasticsearchUtils($client);

        $this->app->bind(ElasticsearchUtils::class, function() use ($utils) {
            return $utils;
        });

        $this->app->singleton('elasticsearch-utils', function() use ($utils) {
            return $utils;
        });
    }
}