<?php

namespace GenerCodeOrm\Builder;

use \GenerCodeOrm\SchemaRepository;
use \GenerCodeOrm\SchemaFactory;
use \GenerCodeOrm\Cells\MetaCell;
use \GenerCodeOrm\Cells\ReferenceTypes;
use \Illuminate\Database\Query\Builder;
use \GenerCodeOrm\Exceptions\CellTypeException;

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
            if ($schema->hasSchema($parent->reference)) {
                $this->joinIn($parent, $schema->get("--id", $parent->reference));
                $ref = $parent->reference;
            } else {
                break;
            }
        } 
    }

    public function secure(SchemaRepository $schema, \GenerCodeOrm\DataSet $data, ?string $to = null) {
        $ref = $to;
        if (!$ref) $ref = "";
        while($schema->has("--parent", $ref)) {
            $parent = $schema->get("--parent", $ref);
            $this->joinIn($parent, $schema->get("--id", $parent->reference));
            $ref = $parent->reference;
        }

        $bind = $data->getBind("--owner");
        $this->filterId($bind->cell->schema->alias . "." . $bind->cell->name, $bind->value);
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


    public function filterId($alias, $values)
    {
        if (is_array($values)) {
            $this->whereIn($alias, $values);
        } else {
            $this->where($alias, "=", $values);
        }
    }


    public function filterTime($alias, $values)
    {
        if (is_array($values)) {
            if ($this->isAssoc($values)) {
                if (isset($values["min"])) {
                    $this->where($alias, ">=", $values["min"]);
                }

                if (isset($values["max"])) {
                    $this->where($alias, "<=", $values["max"]);
                }
            } else {
                $this->whereIn($alias, $values);
            }
        } else {
            $this->where($alias, "=", $values);
        }
    }


    public function filterNumber($alias, $values)
    {
        if (is_array($values)) {
            if ($this->isAssoc($values)) {
                if (isset($values["min"])) {
                    $this->where($alias, ">=", $values["min"]);
                }

                if (isset($values["max"])) {
                    $this->where($alias, "<=", $values["max"]);
                }
            } else {
                $this->whereIn($alias, $values);
            }
        } else {
            $this->where($alias, "=", $values);
        }
    }


    public function filterFlag($alias, $value)
    {
        $this->where($alias, "=", $value);
    }


    public function filterString($alias, $values)
    {
        if (is_array($values)) {
            $this->where(function ($query) use ($cell, $values) {
                $first=array_shift($values);
                $query->where($alias, "like", $first);
                foreach ($values as $val) {
                    $query->orWhere($alias, "like", $val);
                }
            });
        } else {
            $this->where($alias, "=", $values);
        }
    }


    public function filter(\GenerCodeOrm\DataSet $model, $use_alias = true)
    {
        $binds = $model->getBinds();

        foreach ($binds as $alias=>$bind) {
            $cell = $bind->cell;
            $val = $bind->value;
            $ref = new \ReflectionClass($cell);
            $name = $ref->getShortName();
            $alias_name = (!$use_alias) ? $cell->name : $cell->schema->alias . "." . $cell->name;
            if ($name == "IdCell") {
                $this->filterId($alias_name,  $val);
            } elseif ($name == "TimeCell") {
                $this->filterTime($alias_name, $val);
            } elseif ($name == "NumberCell") {
                $this->filterNumber($alias_name, $val);
            } elseif ($name == "FlagCell") {
                $this->filterFlag($alias_name, $val);
            } elseif ($name == "StringCell") {
                $this->filterString($alias_name, $val);
            } else {
                throw new CellTypeException($name);
            }
        }
    }


    public function fields(SchemaRepository $schema, array $cells)
    {
        foreach ($cells as $alias=>$cell) {
            $this->addSelect($cell->schema->alias . "." . $cell->name . " as " . $alias);
            if ($cell->reference_type == ReferenceTypes::REFERENCE) {
                $inner = (!$cell->required) ? false : true;
                $this->joinIn($cell, $schema->get("--id", $alias ), $inner);
            }
        }
    }



    public function multipleUpdateStmt(array $template, array $data)
    {
        $sql = $this->grammar->compileUpdate($this, $template);
        $pdo = $this->connection->getPdo();
        
        try {
            $stmt = $pdo->prepare($sql);
        } catch(\PDOException $e) {
            throw new \GenerCodeOrm\Exceptions\SQLException($sql, [],  $e->getMessage());
        }


        foreach ($data as $dataSet) {
            try {
                $stmt->execute(array_values($dataSet->toCellNameArr()));
            } catch(\PDOException $e) {
                throw new \GenerCodeOrm\Exceptions\SQLException($sql, $dataSet->toCellNameArr(), $e->getMessage());
            }
        }
    }

}
