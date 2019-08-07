<?php

namespace KaidoRen\ELSearch\Console\Commands;

use KaidoRen\ELSearch\Trais\Searchable;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import {model : Class for indexing (\'App\Example\')}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index model to Elasticsearch';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ref = new \ReflectionClass($class = $this->argument('model'));
        if (in_array(Searchable::class, $ref->getTraitNames())) {
            $class::chunk(200, function(Collection $collect) {
                $collect->each(function($model) {
                    app('elasticsearch-utils')->createOrUpdate($model); 
                });
            });
        }
    }
}