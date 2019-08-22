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
     * Enabled or disabled queue
     */
    protected $queue;

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
     * @return void
     */
    public function createOrUpdate(Model $model): void
    {
        $params = $this->getBaseParams($model);

        if ($this->client->exists($params)) {
            $params['body']['doc'] = $model->getSearchableBody();
            $this->client->update($params);
            return;
        }

        $params['body'] = $model->getSearchableBody();
        $this->client->index($params);
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
            $this->client->delete($params);
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
     * Check exists model in Elasticsearch
     *
     * @param Model $model
     * @return bool
     */
    public function exists(Model $model)
    {
        return $this->client->exists($this->getBaseParams($model));
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
