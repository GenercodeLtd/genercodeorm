<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;

class Repository
{
    protected $dbmanager;

    protected $name;
    protected $profile;
    protected $to;
    protected $order;
    protected $limit;
    protected $group;
    protected $children = [];
    protected $fields;
    protected SchemaRepository $schema;


    public function __construct(
        \Illuminate\Database\DatabaseManager $dbmanager,
        SchemaFactory $factory,
        Profile $profile
    )
    {
        $this->dbmanager = $dbmanager;
        $this->profile = $profile;
        $this->repo_schema = new SchemaRepository($factory);
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }


    private function getWhereParams($params)
    {
        $where_params = array_filter($params, function ($var) {
            return (strpos($var, "__") === 0) ? false : true;
        });
    }


    public function buildUpSchema($name, $params)
    {
        $this->repo_schema->loadBase($name);
        if (isset($params["__children"])) {
            $this->repo_schema->loadChildren($params["__children"]);
        }
        if (isset($params["__to"])) {
            $this->repo_schema->loadTo($params["__to"]);
        }
        
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $this->repo_schema->loadToSecure();
        }
    }

    private function getAllFields()
    {
        $schemas = $this->repo_schema->getSchemas();
        $fields = [];
        foreach ($schemas as $slug=>$schema) {
            $alias_slug(!$slug) ? "" : $slug . "/";
            foreach ($schema->cells as $cell) {
                if (!$cell->background) {
                    $fields[] = $alias_slug . $cell->alias;
                }
            }
        }
        return $fields;
    }


    private function expandFields($fields)
    {
        $nfields = [];
        foreach ($fields as $field) {
            if (strpos($field, "*summary") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                $slug_alias = (!$slug) ? "" : $slug . "/";
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cell) {
                    if ($cell->summary) {
                        $nfields[] = $slug_alias . $cfield->alias;
                    }
                }
            } elseif (strpos($field, "*") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                $slug_alias = (!$slug) ? "" : $slug . "/";
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cfield) {
                    $nfields[] = $slug_alias . $cfield->alias;
                }
            } else {
                $nfields[] = $field;
            }
        }
        return $nfields;
    }




    private function buildModel($where_params): DataSet
    {
        $model = new DataSet();

        foreach ($where_params as $alias=>$val) {
            if ($this->repo_schema->has($alias)) {
                $cell = $this->repo_schema->get($alias);
                $model->bind($alias, $cell);
            }
        }

        $model->apply($where_params);
        $model->validate();
        if ($this->repo_schema->isSecure()) {
            $top = $this->repo_schema->getTop();
            $cell = $top->get("--owner");
            $model->bind("--owner", $cell);
            $model->{"--owner"} = $this->profile->id;
        }
        return $model;
    }


    private function buildQuery(DataSet $model)
    {
        //$query = Capsule::table($this->name);
        $map = new Mappers\MapQuery($this->dbmanager, $this->schema);

        $fields = (!isset($params["__fields"])) ? $this->getAllFields() : $this->expandFields($params["__fields"]);
        $this->repo_schema->loadReferences($fields);

        return $map->get($fields, $model, $this->order, $this->limit, $this->group);
    }



    public function get($name, array $params, $all = false)
    {
        if (!$this->profile->hasPermission($name, "get")) {
            throw new \Exception("No permission for " . $name);
        }

        $where_params = $this->getWhereParams($params);
        $this->buildUpSchema($name, $params);

        $model = $this->buildModel($where_params);

        $this->limit = 1;
        $res = $this->buildQuery($model);
        return $res->first();
    }


    public function getAll($name, array $params, $all = false)
    {
        if (!$this->profile->hasPermission($name, "get")) {
            throw new \Exception("No permission for " . $name);
        }

        $where_params = $this->getWhereParams($params);
        $this->buildUpSchema($name, $params);

        $model = $this->buildModel($where_params);

        if (isset($params["__limit"])) $this->limit = $params["__limit"];
        if (isset($params["__order"])) $this->order = $params["__order"];
        if (isset($params["__group"])) $this->group = $params["__group"];

        if ($this->repos_schema->has("--sort")) {
            $this->order = ["--sort"=>"ASC"];
        }
        $res = $this->buildQuery($model);
        return $res->toArray();
    }


    public function getFirst($name, array $params)
    {
        $this->order = ["--id"=>"ASC"];
        return $this->get($name, $params);
    }


    public function getLast($name, array $params)
    {
        $this->order = ["--id"=>"DESC"];
        return $this->get($name, $params);
    }




    public function count(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "get")) {
            throw new \Exception("No permission for " . $name);
        }

        $this->buildUpSchema($name, $params);

        $where_params = $this->getWhereParams($params);
        $model = $this->buildModel($where_params);

      

        $map = $this->buildQuery();
        $collection = $map->query
        ->rawSelect("count(" . $schema->table . "." . $id->name . ") as 'count'")
        ->take(1)
        ->get();

        return $collection->first();
    }
}
