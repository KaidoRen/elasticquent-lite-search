<?php

namespace KaidoRen\ELSearch\Jobs;

use Illuminate\
{
    Bus\Queueable,
    Queue\SerializesModels,
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Foundation\Bus\Dispatchable,
    Database\Eloquent\Model
};

use KaidoRen\ELSearch\Utils\ElasticsearchUtils;

final class DeleteIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle(ElasticsearchUtils $client)
    {
        $client->delete($this->model);
    }
}
