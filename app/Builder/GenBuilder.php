<?php

namespace GenerCodeOrm\Builder;

use GenerCodeOrm\SchemaRepository;
use GenerCodeOrm\SchemaFactory;
use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Cells\ReferenceTypes;
use Illuminate\Database\Query\Builder;

class GenBuilder extends Builder
{

    protected SchemaFactory $factory;

    function setFactory($factory) {
        $this->factory = $factory;
    }
 

    private function joinIn(
        MetaCell $field,
        MetaCell $right_field,
        bool $inner = true
    ): void {
        $left_table = $field->schema->alias;
        $left_id = $left_table . "." . $field->name;
        $right_table = $right_field->schema->table . " as " . $right_field->schema->alias;
        $right_id = $right_field->schema->alias . "." . $right_field->name;
        if (!$inner) {
            $this->leftJoin($right_table, $left_id, '=', $right_id);
        } else {
            $this->join($right_table, $left_id, '=', $right_id);
        }
    }


    public function copy($fields, $query)
    {
        $this->setTable(false);
        $root = $this->repo_schema->getSchema("");

        $fields = [];
        foreach ($data->getBinds() as $alias=>$bind) {
            $fields[] = $bind->cell->name;
        }

        $this->query->insertUsing($fields, $query);
    }



    public function joinTo(SchemaRepository $schema)
    {
        $ref = "";
        while ($schema->has("--parent", $ref)) {
            $parent = $schema->get("--parent", $ref);
            $this->joinIn($parent, $schema->get("--id", $parent->reference));
            $ref = $parent->reference;
        } 
    }

    public function secure(SchemaRepository $schema, \GenerCodeOrm\DataSet $data, $to) {
        $ref = $to;
        while($schema->has("--parent", $ref)) {
            $parent = $schema->get("--parent", $ref);
            $this->joinIn($parent, $schema->get("--id", $parent->reference));
            $ref = $parent->reference;
        }

        $bind = $data->getBind("--owner");
        $this->buildId($bind->cell, $bind->value);
    }


    public function children(SchemaRepository $schema, $ref = "")
    {
        $id = $schema->get("--id", $ref);
        
        foreach ($id->reference as $child) {
            if ($schema->hasSchema($child)) {
                $this->joinIn($id, $schema->get("--parent", $child), false);
                $this->children($schema, $child);
            }
        }
    }


    public function isAssoc(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }


    public function buildId(MetaCell $cell, $values)
    {
        if (is_array($values)) {
            $this->whereIn($cell->schema->alias . "." . $cell->name, $values);
        } else {
            $this->where($cell->schema->alias . "." . $cell->name, "=", $values);
        }
    }


    public function buildTime(MetaCell $cell, $values)
    {
        if (is_array($values)) {
            if ($this->isAssoc($values)) {
                if (isset($values["min"])) {
                    $this->where($cell->schema->alias . "." . $cell->name, ">=", $values["min"]);
                }

                if (isset($values["max"])) {
                    $this->where($cell->schema->alias . "." . $cell->name, "<=", $values["max"]);
                }
            } else {
                $this->whereIn($cell->schema->alias . "." . $cell->name, $values);
            }
        } else {
            $this->where($cell->schema->alias . "." . $cell->name, "=", $values);
        }
    }


    public function buildNumber(MetaCell $cell, $values)
    {
        if (is_array($values)) {
            if ($this->isAssoc($values)) {
                if (isset($values["min"])) {
                    $this->where($cell->schema->alias . "." . $cell->name, ">=", $values["min"]);
                }

                if (isset($values["max"])) {
                    $this->where($cell->schema->alias . "." . $cell->name, "<=", $values["max"]);
                }
            } else {
                $this->whereIn($cell->schema->alias . "." . $cell->name, $values);
            }
        } else {
            $this->where($cell->schema->alias . "." . $cell->name, "=", $values);
        }
    }


    public function buildFlag(MetaCell $cell, $value)
    {
        $this->where($cell->schema->alias . "." . $cell->name, "=", $value);
    }


    public function buildString(MetaCell $cell, $values)
    {
        if (is_array($values)) {
            $this->where(function ($query) use ($cell, $values) {
                $first=array_shift($values);
                $query->where($cell->schema->alias . "." . $cell->name, "like", $first);
                foreach ($values as $val) {
                    $query->orWhere($cell->schema->alias . "." . $cell->name, "like", $val);
                }
            });
        } else {
            $this->where($cell->schema->alias . "." . $cell->name, "=", $values);
        }
    }


    public function filter(\GenerCodeOrm\DataSet $model)
    {
        $binds = $model->getBinds();

        foreach ($binds as $alias=>$bind) {
            $cell = $bind->cell;
            $val = $bind->value;
            $ref = new \ReflectionClass($cell);
            $name = $ref->getShortName();
            if ($name == "IdCell") {
                $this->buildId($cell, $val);
            } elseif ($name == "TimeCell") {
                $this->buildTime($cell, $val);
            } elseif ($name == "NumberCell") {
                $this->buildNumber($cell, $val);
            } elseif ($name == "FlagCell") {
                $this->buildFlag($cell, $val);
            } elseif ($name == "StringCell") {
                $this->buildString($cell, $val);
            } else {
                throw new CellTypeExtension($name);
            }
        }
    }


    public function fields(SchemaRepository $schema, array $cells)
    {
        foreach ($cells as $alias=>$cell) {
            $this->addSelect($cell->schema->alias . "." . $cell->name . " as " . $alias);
            if ($cell->reference_type == ReferenceTypes::REFERENCE) {
                $inner = (!$cell->required) ? false : true;
                $this->joinIn($cell, $schema->get("--id", $alias), $inner);
            }
        }
    }

}
