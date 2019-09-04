<?php

namespace KaidoRen\ELSearch\Observers;

use KaidoRen\ELSearch\
{
    Jobs\CreateOrUpdateIndexJob,
    Jobs\DeleteIndexJob,
    Utils\ElasticsearchUtils
};

use Illuminate\Database\Eloquent\Model;

final class ELSearchObserver
{
    /**
     * @var ElasticsearchUtils
     */
    protected $utils;

    protected $queue;

    /**
     * @param ElasticsearchUtils   $utils
     */
    public function __construct(ElasticsearchUtils $utils)
    {
        $this->utils = $utils;
        $this->queue = config('elsearch.queue.models', false);
    }

    /**
     * Called when the model is created
     *
     * @param Model     $model
     */
    public function created(Model $model)
    {
        $this->queue ?
            dispatch(new CreateOrUpdateIndexJob($model))->delay(now()->addSecond()) :
            $this->utils->createOrUpdate($model);
    }

    /**
     * Called when the model is updated
     *
     * @param Model     $model
     */
    public function updated(Model $model)
    {
        $this->queue ?
            dispatch(new CreateOrUpdateIndexJob($model))->delay(now()->addSecond()) :
            $this->utils->createOrUpdate($model);
    }

    /**
     * Called when the model is deleted
     *
     * @param Model     $model
     */
    public function deleted(Model $model)
    {
        $this->queue ?
            dispatch(new DeleteIndexJob($model))->delay(now()->addSecond()) :
            $this->utils->createOrUpdate($model);
    }
}
