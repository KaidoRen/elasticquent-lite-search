<?php

namespace KaidoRen\ELSearch\Utils;

use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;

class ElasticsearchUtils
{
    /**
     * Elasticsearch client
     * 
     * @var Client
     */
    protected $client;
    
    /**
     * @param Client        $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create or update index into Elasticsearch
     * 
     * @param Model     $model
     * 
     * @return callable|array
     */
    public function createOrUpdate(Model $model)
    {
        $params = $this->getBaseParams($model);
        $method = $this->putBodyParams($model, $params)
            ? 'update' : 'index';

        return $this->client->{$method}($params);
    }

    /**
     * Delete element from Elasticsearch
     * 
     * @param Model     $model
     * 
     * @return callable|array
     */
    public function delete(Model $model)
    {
        $params = $this->getBaseParams($model);

        if ($this->client->exists($params)) {
            return $this->client->delete($params);
        }

        return [];
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