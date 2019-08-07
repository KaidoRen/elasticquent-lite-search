<?php

namespace KaidoRen\ELSearch\Observers;

use KaidoRen\ELSearch\Utils\ElasticsearchUtils;
use Illuminate\Database\Eloquent\Model;

final class ELSearchObserver
{
    /**
     * @var ElasticsearchUtils
     */
    protected $utils;

    /**
     * @param ElasticsearchUtils   $utils
     */
    public function __construct(ElasticsearchUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Called when the model is created
     * 
     * @param Model     $model
     */
    public function created(Model $model)
    {
        $this->utils->createOrUpdate($model);
    }

    /**
     * Called when the model is updated
     * 
     * @param Model     $model
     */
    public function updated(Model $model)
    {
        $this->utils->createOrUpdate($model);
    }

    /**
     * Called when the model is deleted
     * 
     * @param Model     $model
     */
    public function deleted(Model $model)
    {
        $this->utils->delete($model);
    }
}