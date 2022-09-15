<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Str;

class Repository
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

    public function __construct($query, $profile)
    {
        $this->query = $query;
        $this->profile = $profile;
    }

    public function __set($key, $val) {
        if (property_exists($this, $key)) $this->$key = $val;
    }

    public function setModel($name)
    {
        $this->name = $name;
    }

    private function loadSchema($name = null)
    {
        if (!$name) $name = $this->name;
        return SchemaFactory::create($this->name);
    }

    private function archive($model) {
        $archive_schema = $this->loadSchema($this->name . "_archive");
        $schema = $this->loadSchema($this->name);

        $archive_schema->activateCellCategory("archive");
        $schema->activateCellCategory("archive");

        $map = new Mappers/MapCopy($this->query);
        $map->copy($schema, $archive_schema, $model);
    }




    public function get(array $params, $all = false)
    {
        if (!$this->profile->hasPermission($this->name, "get")) {
            throw \Exception();
        }

    
        $schema = $this->loadSchema($this->name);
        


       // $join_map = new Mappers\MapJoins();
       // $join_map->


        $where_params = array_filter($params, function($var) 
            { 
                return (strpos($var, "__") === 0) ? false : true;
            }
        );

        foreach($where_params as $alias=>$val) {
            $schema->addActiveCell($alias, $schema->getFromAlias($alias));
        }

        $model = new Model($where_params);

        Validator::validate($schema, $model);

        $where_map = new Mappers\MapFilters();
        $where_map->buildFilter($schema->getContainer(), $model);

        
        if (isset($params["__to"])) {
            $schema->activateTo($params["__to"]);
        }

        if (isset($params["__children"])) {
            $schema->activateTo($params["__children"]);
        }

        if (isset($params["__fields"])) {
            $schema->activateFields($params["__fields"]);
        } else {
            $schema->activateFields("*");
        }

        $join = new Mappers\JoinMaps($this->query);
        $join->buildUp($schema);
        $join->buildDown($schema);
        
       
        //now run group by

        $model->setLimit(1);

        $validator->validate();

        $collection = $model->get();
        

        $res = ($all) ? $this->query->getAll() : $this->query->get();
        trigger($this->name, "select", $res);
    }




    public function count(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "get")) {
            throw \Exception();
        }

    
        $schema = $this->loadSchema($this->name);
        


       // $join_map = new Mappers\MapJoins();
       // $join_map->


        $where_params = array_filter($params, function($var) 
            { 
                return (strpos($var, "__") === 0) ? false : true;
            }
        );

        foreach($where_params as $alias=>$val) {
            $schema->addActiveCell($alias, $schema->getFromAlias($alias));
        }

        $model = new Model($where_params);

        $where_map = new Mappers\MapFilters();
        $where_map->buildFilter($schema->getContainer(), $model);

        
        if (isset($params["__to"])) {
            $schema->activateTo($params["__to"]);
        }

        if (isset($params["__children"])) {
            $schema->activateTo($params["__children"]);
        }

        $id = $schema->getActiveCol("--id");
        $this->query->rawSelect("count(" . $schema->table . "." . $id->name . ") as 'count'")->take(1);
        return $this->query->get();
    }


    private function parseIncomingMetaData($params, $schema, $query)
    {
        foreach ($params as $key=>$value) {
            if ($key == "__to") {
                $schema->activateTo($value);
            } elseif ($key == "__fields") {
                $this->query->select($value);
            } elseif ($key == "__children") {
                $schema->activateChildren($value);
            } elseif ($key == "__group") {

              /*  setGroup(String $group) {
                    $parse_col = $this->parseName($col);
                    $col = $this->getCol($parse_col);
                    if (!$col) {
                        throw "Column doesn't exist error";
                    }
                    $this->query->groupBy($group);
                $model->setGroup($val); */
            } elseif ($key == "__order") {
                $model->setOrder($val);
            } elseif ($key == "__limit") {
                $model->setLimit($val);
            }
        }
    }
}
