<?php

namespace KaidoRen\ELSearch\Utils;

use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;
use KaidoRen\ELSearch\Jobs\ELSearchJob as Job;

class ElasticsearchUtils
{
    /**
     * Elasticsearch client
     * 
     * @var Client
     */
    protected $client;

    /**
     * Enabled or disabled queue
     */
    protected $queue;
    
    /**
     * @param Client        $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->queue = config('elsearch.queue', true);
    }

    /**
     * Create or update index into Elasticsearch
     * 
     * @param Model     $model
     * 
     * @return void
     */
    public function createOrUpdate(Model $model): void
    {
        $params = $this->getBaseParams($model);
        $method = $this->putBodyParams($model, $params)
            ? 'update' : 'index';

        if (!$this->queue) {
            $this->client->{$method}($params);
            return;
        }

        dispatch(new Job($method, $params));
    }

    /**
     * Delete element from Elasticsearch
     * 
     * @param Model     $model
     * 
     * @return void
     */
    public function delete(Model $model): void
    {
        $params = $this->getBaseParams($model);

        if ($this->client->exists($params)) {
            if (!$this->queue) {
                $this->client->delete($params);
                return;
            }

            dispatch(new Job('delete', $params));
        }
    }

    /**
     * Search query
     * 
     * @param string        $index
     * @param string        $query
     * @param array         $filterIds
     * @param string        $defaultOperator
     * 
     * @return callable|array
     */
    public function search(string $index, string $query, array $filterIds = [], string $defaultOperator = 'OR')
    {
        $params['index'] = $index;
        $params['body']['query']['bool']['must']['query_string']['query'] = "*{$query}*";
        $params['body']['query']['bool']['must']['query_string']['default_operator'] = $defaultOperator;

        if (count($filterIds)) {
            $params['body']['query']['bool']['filter']['terms']['_id'] = $filterIds;
        }

        return $this->client->search($params); 
    }

    /**
     * 
     * @param Model     $model
     * @param array     $params
     * 
     * @return bool
     */
    protected function putBodyParams(Model $model, array &$params): bool
    {
        $body = $model->getSearchableBody();
        
        if ($this->client->exists($params)) {
            $params['body']['doc'] = $body;
            return true;
        }

        $params['body'] = $body;
        
        return false;
    }

    /**
     * @param Model     $model
     * 
     * @return array
     */
    protected function getBaseParams(Model $model)
    {
        return [
            'index'     => $model->getSearchableIndex(),
            'type'      => $model->getSearchableType(),
            'id'        => $model->getKey() 
        ];
    }
}