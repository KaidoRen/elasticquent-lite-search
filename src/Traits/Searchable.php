<?php

namespace KaidoRen\ELSearch\Trais;

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
}