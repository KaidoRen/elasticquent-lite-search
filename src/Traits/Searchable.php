<?php

namespace KaidoRen\ELSearch\Trais;

use KaidoRen\ELSearch\Observers\ELSearchObserver;

trait Searchable
{
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

    public static function bootSearchable()
    {
        static::observe(ELSearchObserver::class);
    }
}