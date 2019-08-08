<?php

namespace KaidoRen\ELSearch\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Elasticsearch\Client;

final class ELSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Method and params for Elasticsearch functions.
     * 
     * @var string , @var array
     */
    protected $method, $params;

    /**
     * Create a new job instance.
     *
     * @param string        $method
     * @param array         $params
     * 
     * @return void
     */
    public function __construct(string $method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @param  Client  $client
     * 
     * @return void
     */
    public function handle(Client $client)
    {
        $client->{$this->method}($this->params);
    }
}