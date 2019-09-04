<?php

namespace KaidoRen\ELSearch\Console\Commands;

use KaidoRen\ELSearch\
{
    SearchableModel,
    Jobs\CreateOrUpdateIndexJob
};
use Illuminate\Support\Collection;
use Illuminate\Console\Command;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:import {model : Class for indexing (\'App\Example\')}
                            {--chunks=100 : Number of records retrieved at a time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index model to Elasticsearch';

    protected const DEFAULT_CHUNKS = 100;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('model');
        $ref = new \ReflectionClass($class);

        if (SearchableModel::class === $ref->getParentClass()->getName()) {
            $bar = $this->output->createProgressBar();

            if (!$count = $class::count()) {
                return;
            }

            $bar->setMaxSteps($count);
            $bar->start();

            $queue = config('elsearch.queue.commands.import', false);
            $chunks = ($value = $this->option('chunks')) <= 0 ? DEFAULT_CHUNKS : $value;

            $class::chunk($chunks, function(Collection $collect) use ($bar, $queue) {
                $collect->each(function($model) use ($bar, $queue) {
                    $queue ? 
                    dispatch(new CreateOrUpdateIndexJob($model))->delay(now()->addSecond()) :
                    app('elasticsearch-utils')->createOrUpdate($model);
                    $bar->advance();
                });
            });

            $bar->finish();

            $this->line("\r\n<fg=cyan>Import models from $class successfully completed!</>");
        }
    }
}
