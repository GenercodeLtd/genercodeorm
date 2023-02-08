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

    use Children;

    protected $to;
    protected $fields = [];
    protected $children = [];
    protected $limit = 0;
    protected $offset = 0;
    protected $order = [];
    protected $filters = [];

    



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


    public function apply($arr) {
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
