<?php

namespace GenerCodeOrm\Mappers;

use GenerCodeOrm\SchemaContainer;
use GenerCodeOrm\Model;
use GenerCodeOrm\Cells\MetaCell;

class MapFilters
{
    private $query;
 

    public function __construct($query)
    {
        $this->query = $query;
    }


    private function isAssoc(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }


    private function buildId($table, MetaCell $cell, array $values)
    {
        $this->query->whereIn($table . "." . $cell->name, $values);
    }


    private function buildTime($table, MetaCell $cell, $values)
    {
        if ($this->isAssoc($values)) {
            if (isset($values["min"])) {
                $this->query->where($table . "." . $cell->name, ">=", $values["min"]);
            }

            if (isset($values["max"])) {
                $this->query->where($table . "." . $cell->name, "<=", $values["max"]);
            }
        } else {
            $this->query->whereIn($table . "." . $cell->name, $values);
        }
    }


    private function buildNumber($table, MetaCell $cell, array $values)
    {
        if ($this->isAssoc($values)) {
            if (isset($values["min"])) {
                $this->query->where($table . "." . $cell->name, ">=", $values["min"]);
            }

            if (isset($values["max"])) {
                $this->query->where($table . "." . $cell->name, "<=", $values["max"]);
            }
        } else {
            $this->query->whereIn($table . "." . $cell->name, $values);
        }
    }


    private function buildFlag($table, MetaCell $cell, $value)
    {
        $this->query->where($table . "." . $cell->name, "=", $value);
    }


    private function buildString($table, MetaCell $cell, array $values)
    {
        $this->query->where(function ($query) use ($table, $cell, $values) {
            $first=array_shift($values);
            $query->where($table . "." . $cell->name, "like", $first);
            foreach ($values as $val) {
                $query->orWhere($table . "." . $cell->name, "like", $val);
            }
        });
    }


    public function buildFilter(SchemaContainer $container, Model $model)
    {
        $values = $model->getValues();
        foreach ($values as $container_slug=>$data) {
            $container = $container->get($container_slug);

            foreach ($data as $alias=>$val) {
                $cell = $container->getActiveCell($alias);
                $ref = new \ReflectionClass($cell);
                $name = $ref->getShortName();
                if ($name == "IdCell") {
                    $this->buildId($container->table, $cell, $val);
                } elseif ($name == "TimeCell") {
                    $this->buildTime($container->table, $cell, $val);
                } elseif ($name == "NumberCell") {
                    $this->buildNumber($container->table, $cell, $val);
                } elseif ($name == "FlagCell") {
                    $this->buildFlag($container->table, $cell, $val);
                } elseif ($name == "StringCell") {
                    $this->buildString($container->table, $cell, $val);
                } else {
                    throw "Type: " . $name . " is not supported";
                }
            }
        }
    }
}
