<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;

class Repository extends Mappers\Map
{
    protected $name;
    protected $profile;
    protected $query;
    protected $to;
    protected $order;
    protected $limit;
    protected $group;
    protected $children = [];
    protected $fields;
    protected SchemaRepository $schema_resp;


    public function __construct($profile)
    {
        $this->profile = $profile;
        $this->schema = new SchemaRepository();
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }

    public function setModel($name)
    {
        $this->name = $name;
    }

    private function getWhereParams($params)
    {
        $where_params = array_filter($params, function ($var) {
            return (strpos($var, "__") === 0) ? false : true;
        });
    }

    private function loadReferenceSchema($slug, Cells\MetaCell $cell)
    {
        $this->schema->load($slug . "/" . $cell->name . "/" . $cell->reference . "/", SchemaFactory::get($cell->reference));
    }


    private function loadChildrenSchema(Cells\MetaCell $id)
    {
        foreach ($id->reference as $child) {
            if (in_array($child, $children)) {
                $this->schema->load($child . "/", SchemaFactory::get($child));
                $this->loadChildrenSchema($this->schema->get($child . "/--id"));
            }
        }
    }

    private function loadSchema($name = null)
    {
        if (!$name) {
            $name = $this->name;
        }
        $this->schema->create($this->name);

        if ($this->to) {
            $parent = $this->schema->get("--parent");

            while ($parent) {
                $this->schema->load($parent->reference . "/", SchemaFactory::get($parent->reference));
                if ($parent->reference == $this->to) {
                    break;
                }
                if ($this->schema->has($parent->reference . "/--parent")) {
                    $parent = $this->schema->get($parent->reference . "/--parent");
                } else {
                    $parent = null;
                }
            }
        }
    }


    private function buildModel($where_params): DataSet
    {
        $model = new DataSet();

        foreach ($where_params as $alias=>$val) {
            if ($this->schema->has($alias)) {
                $cell = $this->schema->get($alias);
                $model->bind($alias, $cell);
            }
        }

        $model->apply($where_params);
        $model->validate();

        return $model;
    }

    private function buildChildren($id)
    {
        foreach ($id->reference as $child) {
            if ($this->schema->hasSchema($child . "/")) {
                $this->buildJoin($query, $id, $this->schema->get($child . "/--parent"));
                $id = $this->schema->get($child . "/--id");
            }
        }
    }


    private function buildQuery(DataSet $model)
    {
        //$query = Capsule::table($this->name);
        $map = new Mappers\MapQuery($this->name);
        $map->buildFilters($model);
        if ($this->to) {
            $map->buildTo($this->to);
        }
        if ($this->children) {
            $map->buildChildren($this->children);
        }
        return $map;
    }



    public function get(array $params, $all = false)
    {
        if (!$this->profile->hasPermission($this->name, "get")) {
            throw \Exception();
        }

        $where_params = $this->getWhereParams($params);
        $schema = $this->loadSchema($this->name);
        $model = $this->buildModel($where_params);


        // $join_map = new Mappers\MapJoins();
        // $join_map->


        $fields = [];
        if ($this->fields) {
            $fields = $this->fields;
        } else {
            //build up a new one
            $schemas = $schema->getSchemas();
            foreach ($schemas as $slug=>$schema) {
                foreach ($schema as $alias=>$cell) {
                    if ($slug AND !$cell->summary) continue;
                    $fields[] = $alias;
                    if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
                        $this->loadReferenceSchema($slug . "/" . $alias, $cell);
                        $ref_alias = $slug . "/" . $alias . "/" . $cell->reference;
                        $schemas[$ref_alias] = $schema->getSchema($ref_alias);
                    }
                }
            }
        }
        //get all fields required


        $map = $this->buildQuery($model);
        $map->buildFields($fields);
        $map->buildOrder($this->order);
        if ($this->limit) {
            $map->buildLimit();
        }
        if ($this->group) {
            $map->buildGroup();
        }
        $collection = $map->query->get();

        $res = ($all) ? $map->query->getAll() : $map->query->get();
        trigger($this->name, "select", $res);
    }




    public function count(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "get")) {
            throw \Exception();
        }
        $model = $this->buildModel($params);


        $schema = $this->loadSchema($this->name);

        $map = $this->buildQuery();
        $collection = $map->query
        ->rawSelect("count(" . $schema->table . "." . $id->name . ") as 'count'")
        ->take(1)
        ->get();

        // $join_map = new Mappers\MapJoins();
        // $join_map->
    }
}
