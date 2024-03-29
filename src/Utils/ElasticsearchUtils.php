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

        if (!$this->client->indices()->exists(['index' => $params['index']])) {
            $this->createMappingsAndSettings($model);
        }

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
    public function search(string $index, string $query, array $filterIds = [], string $operator = 'OR')
    {
        $params['index'] = $index;
        $params['body']['query']['bool']['must']['multi_match'] = [
            'query' => $query,
            'fields' => ['*'],
            'type' => 'phrase_prefix',
            'operator' => $operator
        ];

        if (count($filterIds)) {
            $params['body']['query']['bool']['filter']['ids']['values'] = $filterIds;
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

    /**
     * Create index with mappings and settings
     *
     * @param Model     $model
     *
     * @return void
     */
    protected function createMappingsAndSettings(Model $model): void
    {
        $params['index'] = $model->getSearchableIndex();

        if (count($settings = $model->getSearchableSettings())) {
            $params['body']['settings'] = $settings;
        }

        if (count($mappings = $model->getSearchableMappings())) {
            $params['body']['mappings'] = $mappings;
        }

        $this->client->indices()->create($params);
    }
}
