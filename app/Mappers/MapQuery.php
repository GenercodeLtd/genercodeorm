<?php

namespace GenerCodeOrm\Mappers;

use GenerCodeOrm\SchemaRepository;
use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Cells\ReferenceTypes;

class MapQuery
{
    protected $query;
    protected $schema;
    protected $connection;

    public function __construct(\Illuminate\Database\DatabaseManager $dbmanager, SchemaRepository $schema)
    {
        $this->schema = $schema;
        $this->connection = $dbmanager->connection();
    }


    public function getQuery()
    {
        return $this->query;
    }

    public function setTable($with_alias = true)
    {
        $root = $this->schema->getSchema("");
        $this->query = (!$with_alias) ? $this->connection->table($root->table) : $this->connection->table($root->table, $root->alias);
    }


    private function join(
        MetaCell $field,
        MetaCell $right_field,
        bool $inner = true
    ): void {
        $left_table = $field->schema->alias;
        $left_id = $left_table . "." . $field->name;
        $right_table = $right_field->schema->table . " as " . $right_field->schema->alias;
        $right_id = $right_field->schema->alias . "." . $right_field->name;
        if (!$inner) {
            $this->query->leftJoin($right_table, $left_id, '=', $right_id);
        } else {
            $this->query->join($right_table, $left_id, '=', $right_id);
        }
    }


    public function archive(array $data)
    {
        $root = $this->schema->getSchema("");
        $this->query = $this->connection->table($root->table . "_archive");


        /* $binds = $data->getBinds();
         foreach ($binds as $alias=>$bind) {
             $cols[$bind->cell->name] = $bind->value;
         }*/
        return $this->query->insert($data);
    }


    public function post(\GenercodeOrm\DataSet $data)
    {
        $this->setTable(false);

        $binds = $data->getBinds();
        foreach ($binds as $alias=>$bind) {
            $cols[$bind->cell->name] = $bind->value;
        }
        return $this->query->insertGetId($cols);
    }


    public function delete(\GenercodeOrm\DataSet $data)
    {
        $this->setTable();

        $bind = $data->getBind("--id");
        $this->query->where($bind->cell->schema->alias . "." . $bind->cell->name, $bind->value);

        $this->joinTo();
        $this->children();
        $this->secure($data);
        $this->query->delete();
    }


    public function update(\GenercodeOrm\DataSet $data)
    {
        $this->setTable();
        $bind = $data->getBind("--id");
        $this->query->where($bind->cell->name, $bind->value);

        $cols = [];
        $binds = $data->getBinds();
        ;
        foreach ($binds as $alias => $bind) {
            if ($alias == "--id") {
                continue;
            }
            $cols[$bind->cell->name] = $bind->value;
        }
        $this->secure($data);

        $this->query->update($cols);
    }


    public function select(\GenercodeOrm\DataSet $data)
    {
        $this->setTable();
        $bind = $data->getBind("--id");
        $this->joinTo();
        $this->secure($data);
        $this->query->where($bind->cell->schema->alias . "." . $bind->cell->name, $bind->value);
        return $this->query->get();
    }


    public function get(array $fields, \GenercodeOrm\DataSet $data, $order = [], $limit = null, $group = [])
    {
        $this->setTable();
        $this->joinTo();
        $this->children();
        $this->secure();
        $this->fields($fields);
        $this->filter($data);
        $this->order($order);
        if ($limit) $this->limit($limit);
        if ($group) $this->group($group);
        return $this->query->get();
    }


    public function multipleUpdate(array $data)
    {
        if (count($data) == 0) {
            return;
        }

        $this->setTable();

        $root = $this->schema->getSchema("");

        $dataSet = $data->first;

        $cols = [];
        foreach ($dataSet->values as $alias =>$bind) {
            if ($alias == "--id") {
                continue;
            }
            $cols[] = "`" . $bind->cell->schema->alias . "`.`" . $bind->cell->name . "` = ?";
        }

        $id = $dataSet->{"--id"};
        $sql = "UPDATE `" . $root->table . "` AS `" . $root->alias . "` SET ";
        $sql .= implode(", ", $cols);
        $sql .= " WHERE `" . $id->cell->schema->alias . "`.`" . $id->cell->name . "` = ?";

        $pdo = $this->connection->getPdo();

        try {
            $stmt = $pdo->prepare($sql);
        } catch(\PDOException $e) {
            throw new Exceptions\SQLException($sql, $args, $e->getMessage());
        }


        foreach ($data as $dataSet) {
            try {
                $stmt->execute($dataSet->toArr());
            } catch(\PDOException $e) {
                throw new Exceptions\SQLException($this->sql, $args, $e->getMessage());
            }
        }
    }


    public function copy($fields, $query)
    {
        $this->setTable(false);
        $root = $this->schema->getSchema("");

        $fields = [];
        foreach ($data->getBinds() as $alias=>$bind) {
            $fields[] = $bind->cell->name;
        }

        $this->query->insertUsing($fields, $query);
    }

    public function secure($data)
    {
        if ($this->schema->isSecure()) {
            $top = $this->schema->getTop();
            $owner = $top->get("--owner");
            $this->query->where($top->alias . "." . $owner->name, "=", $data->{ "--owner"});
        }
    }


    public function joinTo()
    {
        $parent = $this->schema->get("--parent");
        while ($parent) {
            if ($this->schema->hasSchema($parent->reference)) {
                $this->join($parent, $this->schema->get($parent->reference . "/--id"));
                if ($this->schema->has($parent->reference . "/--parent")) {
                    $parent = $this->schema->get($parent->reference ."/--parent");
                } else {
                    break;
                }
            } else {
                break;
            }
        }
    }

    public function children(MetaCell $id = null)
    {
        if (!$id) {
            $id = $this->schema->get("--id");
        }
        foreach ($id->reference as $child) {
            if ($this->schema->hasSchema($child)) {
                $this->join($id, $this->schema->get($child . "/--parent"), false);
                $this->children($this->schema->get($child . "/--id"));
            }
        }
    }


    public function filter(DataSet $model)
    {
        $filters = new Mappers\MapFilters($query);

        $binds = $model->values;

        foreach ($binds as $alias=>$bind) {
            $ref = new \ReflectionClass($cell);
            $name = $ref->getShortName();
            if ($name == "IdCell") {
                $filters->buildId($bind->table, $cell, $val);
            } elseif ($name == "TimeCell") {
                $filters->buildTime($bind->table, $cell, $val);
            } elseif ($name == "NumberCell") {
                $filters->buildNumber($bind->table, $cell, $val);
            } elseif ($name == "FlagCell") {
                $filters->buildFlag($bind->table, $cell, $val);
            } elseif ($name == "StringCell") {
                $filters->buildString($bind->table, $cell, $val);
            } else {
                throw "Type: " . $name . " is not supported";
            }
        }
    }


    public function fields($fields)
    {
        foreach ($fields as $slug=>$group) {
            $schema = $this->schema->getSchema($slug);
            $output_alias = (!$slug) ? "" : $slug . "/";
            foreach ($group as $field) {
                $cell = $schema->get($field);
                $this->query->addSelect($cell->schema->alias . "." . $cell->name . " as " . $output_alias . $field);
                if ($cell->reference_type == ReferenceTypes::REFERENCE) {
                    $inner = (!$cell->required) ? false : true;
                    $this->join($cell, $this->schema->get($output_alias .  $field . "/--id"), $inner);
                }
            }
        }
    }


    public function order(SchemaRepository $schema, array $orders)
    {
        foreach ($orders as $alias->$dir) {
            $cell = $schema->get($alias);
            $this->query->orderBy($cell->schema->table . "." . $cell->name, $dir);
        }
    }


    public function limit($limit, $offset = 0)
    {
        $this->query->skip($offset)->take($limit);
    }


    public function group(SchemaRepository $schema, String $group)
    {
        $cell = $schema->get($group);
        $this->query->groupBy($cell->schema->table . "." . $cell->name);
    }
}
