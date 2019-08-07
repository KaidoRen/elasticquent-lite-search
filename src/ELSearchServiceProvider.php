<?php

namespace KaidoRen\ELSearch;

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