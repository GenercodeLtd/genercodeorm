<?php

namespace GenerCodeOrm\Models;

use Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Model;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;
use \GenerCodeOrm\Entity;
use \GenerCodeOrm\Builder as Builder;

class Repository extends App
{
    protected $to;
    protected $fields = [];
    protected $children = [];
    protected $limit = 0;
    protected $offset = 0;
    protected $order = [];
    protected $filters = [];

    private function findChildLeaves(array $children, Entity $entity = null)
    {
        $factory =  app()->get("entity_factory");
        if (!$entity) {
            $entity = $this->entities[""];
        }
        $id = $entity->get("--id");

        $matches = [];
        foreach ($id->reference as $child) {
            if (in_array($child, $children)) {
                $peek = ($factory)->create($child);
                if (!$peek) {
                    $matches[$child] = $peek;
                    continue;
                }

                $res = $this->findChildLeaves($children, $peek);
                if (!$res) {
                    $matches[$child] = $peek;
                    continue;
                }

                //if we get this far, then we new matches
                $matches = array_merge($matches, $res);
            } else {
                $peek = ($factory)->create($child);
                $res = $this->findChildLeaves($children, $peek);
                $matches = array_merge($matches, $res);
            }
        }

        return $matches;
    }



    private function getRow($rows, $id)
    {
        $filtered = array_filter($rows, function ($row) use ($id) {
            return $row->{"--id"} == $id;
        });

        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
    }


    private function tidyChildren($obj)
    {
        foreach ($obj as $key=>$val) {
            if (is_array($val)) {
                $obj->$key = array_values($val);
                foreach ($obj->$key as $cval) {
                    $this->tidyChildren($cval);
                }
            }
        }
    }



    private function addChildren($name, $model, &$rows)
    {
        $factory =  app()->get("entity_factory");
        if (!is_array($this->children)) {
            $this->children = [$this->children];
        }
        $leaves = $this->findChildLeaves($this->children, $model->root);

        $ids = [];

        foreach ($rows as $row) {
            $ids[] = $row->{"--id"};
        }

        $idCell = $model->root->get("--id");

        foreach ($idCell->reference as $branch) {
            $entity = ($factory)->create($branch);
            $leaves = $this->findChildLeaves($this->children, $entity);

            if (!$leaves) {
                $child_model = $this->model($branch);
                $child_model->root->slug = $branch;
                $bind = new Binds\SetBind($child_model->root->get("--parent"), $ids);
                $child_model->filter($bind);
                $child_model->fields();

                if ($child_model->root->has("--sort")) {
                    $orderSet = new InputSet($branch);
                    $orderSet->data(["--sort"=>"ASC"]);
                    $child_model->order($orderSet);
                }

                $results = new Builder\ResultsTree($child_model->entities);

                $cursor = $child_model->setFromEntity()->cursor();

                foreach ($cursor as $result) {
                    $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                    $results->toTree($orig, $result, $child_model->root);
                }
            } else {
                foreach ($leaves as $leaf=>$entity) {
                    $child_model = $this->model($leaf);
                    $child_model->to($branch);
                    $child_model->fields();

                    if ($child_model->root->has("--sort")) {
                        $orderSet = new InputSet($leaf);
                        $orderSet->data(["--sort"=>"ASC"]);
                        $child_model->order($orderSet);
                    }

                    $bind = new Binds\SetBind($child_model->entities[$branch]->get("--parent"), $ids);
                    $child_model->filter($bind);

                    $results = new Builder\ResultsTree($child_model->entities);
                    $cursor = $child_model->setFromEntity()->cursor();

                    foreach ($cursor as $result) {
                        $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                        $results->toTree($orig, $result, $child_model->entities[$branch]);
                    }
                }
            }
        }

        foreach($rows as $row) {
            $this->tidyChildren($row);
        }
    }





    private function buildStructure($model)
    {
        //define the structure
        if ($this->to) {
            $model->to($this->to);
        }

        if ($this->fields) {
            $set = new InputSet($this->name);
            $set->data($this->fields);
            $model->fields($set);
        } else {
            $model->fields();
        }
    }


    private function getWhere(): InputSet
    {
        $where = [];
        $set = new InputSet($this->name);
        foreach ($this->filters as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                $set->addData($key, $val);
            }
        }
        return $set;
    }


    private function setLimit($model)
    {
        if ($this->offset) {
            $model->skip($this->offset);
        }
        if ($this->limit) {
            $model->take($this->limit);
        }
    }



    public function get()
    {
       
        $model= $this->builder();

        $this->buildStructure($model);


        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        
        $where = $this->getWhere();

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $this->setLimit($model);

        if ($model->root->has("--sort")) {
            $orderSet = new InputSet($this->name);
            $orderSet->data(["--sort"=>"ASC"]);
            $model->order($orderSet);
        } else if ($this->order) {
            $orderSet = new InputSet($this->name);
            $orderSet->data($this->order);
            $model->order($orderSet);
        }

        $res = $model->setFromEntity()->get()->toArray();
        if ($this->children) {
            $this->addChildren($this->name, $model, $res);
        }
        return $this->trigger("get", $res);
    }


    public function getActive()
    {
        $model= $this->builder();

        $this->buildStructure($model);

        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $where = $this->getWhere();

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);


        if ($model->root->has("--sort")) {
            $orderSet = new InputSet($this->name);
            $orderSet->data(["--sort"=>"ASC"]);
            $model->order($orderSet);
        } else if ($this->order) {
            $orderSet = new InputSet($this->name);
            $orderSet->data($this->order);
            $model->order($orderSet);
        }

        
        $model->take(1);

        $res = $model->setFromEntity()->get()->first();
        if ($res) {
            if ($this->children) {
                $this->addChildren($this->name, $model, [$res]);
            }
        } else {
            $res = new \StdClass;
        }
        return $this->trigger("get", $res);
    }



    public function getFirst()
    {
        $this->order["--id"] = "ASC";
        $this->offset = 0;
        $this->limit = 1;
        return $this->getActive();
    }


    public function getLast()
    {
        $this->order["--id"] = "DESC";
        $this->offset = 0;
        $this->limit = 1;
        return $this->getActive();
    }


    public function count()
    {
        $model= $this->builder();

        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $this->buildStructure($model);

        $where = $this->getWhere();

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $id = $model->root->get("--id");
        $model->select($model->raw("count(" . $id->getDBAlias() . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        return  $model->get()->first();
    }


    public function applyParams($arr) {
        foreach($arr as $key=>$val) {
            if (substr($key, 0, 2) == "__") {
                $key = substr($key, 2);
                $this->$key = $val;
            } else {
                $this->filters[$key] = $val;
            }
        }
    }

   
}
