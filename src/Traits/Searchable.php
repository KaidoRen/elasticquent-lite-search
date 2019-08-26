<?php

namespace KaidoRen\ELSearch\Trais;

use KaidoRen\ELSearch\Observers\ELSearchObserver;
use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    protected $searchableSettings = [];
    protected $searchableMappings = [];

    public function getSearchableSettings(): array
    {
        return $this->searchableSettings;
    }

    public function getSearchableMappings(): array
    {
        return $this->searchableMappings;
    }
    
    public function getSearchableIndex(): string
    {
        return $this->getTable();
    }

    public function getSearchableType(): string
    {
        return $this->getTable();
    }

    public function getSearchableBody(): array
    {
        return $this->toArray();
    }

    public function scopeSearch(Builder $query, string $keywords)
    {
        $IDs = $query->get()->pluck($this->getKeyName())->toArray();
        $response = app('elasticsearch-utils')
            ->search($this->getSearchableIndex(), $keywords, $IDs);
        
        $IDs = array_column($response['hits']['hits'], '_id');

        return $query->whereIn($this->getKeyName(), $IDs);
    }

    public static function bootSearchable()
    {
        static::observe(ELSearchObserver::class);
    }
}