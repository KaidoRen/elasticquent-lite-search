<?php

namespace KaidoRen\ELSearch;

use Illuminate\Database\Eloquent\Model;
use KaidoRen\ELSearch\Trais\Searchable;

abstract class SearchableModel extends Model
{
    use Searchable;
}
