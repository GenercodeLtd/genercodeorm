<?php

namespace GenerCodeOrm\Mappers;

use GenerCodeOrm\Model;
use GenerCodeOrm\Cells\MetaCell;

class MapFilters
{
    private $query;
 

    public function __construct($query)
    {
        $this->query = $query;
    }


    public function isAssoc(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }


    public function buildId(MetaCell $cell, array $values)
    {
        $this->query->whereIn($cell->schema->alias . "." . $cell->name, $values);
    }


    public function buildTime(MetaCell $cell, $values)
    {
        if ($this->isAssoc($values)) {
            if (isset($values["min"])) {
                $this->query->where($cell->schema->alias . "." . $cell->name, ">=", $values["min"]);
            }

            if (isset($values["max"])) {
                $this->query->where($cell->schema->alias . "." . $cell->name, "<=", $values["max"]);
            }
        } else {
            $this->query->whereIn($cell->schema->alias . "." . $cell->name, $values);
        }
    }


    public function buildNumber(MetaCell $cell, array $values)
    {
        if ($this->isAssoc($values)) {
            if (isset($values["min"])) {
                $this->query->where($cell->schema->alias . "." . $cell->name, ">=", $values["min"]);
            }

            if (isset($values["max"])) {
                $this->query->where($cell->schema->alias . "." . $cell->name, "<=", $values["max"]);
            }
        } else {
            $this->query->whereIn($cell->schema->alias . "." . $cell->name, $values);
        }
    }


    public function buildFlag(MetaCell $cell, $value)
    {
        $this->query->where($cell->schema->alias . "." . $cell->name, "=", $value);
    }


    public function buildString(MetaCell $cell, array $values)
    {
        $this->query->where(function ($query) use ($cell, $values) {
            $first=array_shift($values);
            $query->where($cell->schema->alias . "." . $cell->name, "like", $first);
            foreach ($values as $val) {
                $query->orWhere($cell->schema->alias . "." . $cell->name, "like", $val);
            }
        });
    }

}
