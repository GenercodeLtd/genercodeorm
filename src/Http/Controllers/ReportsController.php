<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Model;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;
use \GenerCodeOrm\Entity;
use \GenerCodeOrm\Builder as Builder;

class ReportsController extends AppController
{


    private function buildStructure($model, $name, array $arr)
    {
        //define the structure
        if (isset($arr["__to"])) {
            $model->to($arr["__to"]);
        }

        if (isset($arr["__fields"])) {
            $set = new InputSet($name);
            $set->data($arr["__fields"]);
            $model->fields($set);
        } else {
            $model->fields();
        }

        if (isset($arr["__agg"])) {
            $set = new InputSet($name);
            $set->data($arr[""]);
        }
    }


    private function getWhere($name, array $params): InputSet
    {
        $where = [];
        $set = new InputSet($name);
        foreach ($params as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                $set->addData($key, $val);
            }
        }
        return $set;
    }


    private function setLimit($model, array $params)
    {
        if (isset($params["__offset"])) {
            $model->skip($params["__offset"]);
        }
        if (isset($params["__limit"])) {
            $model->take($params["__limit"]);
        }
    }



    public function get(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);


        $arr = $params->toArray();
        $this->buildStructure($model, $name, $arr);


        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        
        $where = $this->getWhere($name, $params->toArray());

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $this->setLimit($model, $arr);

        if ($model->root->has("--sort")) {
            $orderSet = new InputSet($name);
            $orderSet->data(["--sort"=>"ASC"]);
            $model->order($orderSet);
        } else if (isset($params["__order"])) {
            $orderSet = new InputSet($name);
            $orderSet->data($params["__order"]);
            $model->order($orderSet);
        }

        $res = $model->setFromEntity()->get()->toArray();
        if (isset($params["__children"])) {
            $this->addChildren($name, $model, $params["__children"], $res);
        }
        return $this->trigger($name, "get", $res);
    }


    public function getActive(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        $arr = $params->toArray();
        $this->buildStructure($model, $name, $arr);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $where = $this->getWhere($name, $arr);

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);


        if ($model->root->has("--sort")) {
            $orderSet = new InputSet($name);
            $orderSet->data(["--sort"=>"ASC"]);
            $model->order($orderSet);
        } else if (isset($params["__order"])) {
            $orderSet = new InputSet($name);
            $orderSet->data($params["__order"]);
            $model->order($orderSet);
        }

        
        $model->take(1);

        $res = $model->setFromEntity()->get()->first();
        if ($res === null) {
            $res = new \StdClass();
        } else {
            if (isset($params["__children"])) {
                $arr = [$res];
                $this->addChildren($name, $model, $params["__children"], $arr);
            }
        }
        return $this->trigger($name, "get", $res);
    }



    public function getFirst($name, Fluent $params)
    {
        $params["__order"] = ["--id", "ASC"];
        $params["__offset"] = 0;
        $params["__limit"] = 1;
        return $this->getActive($name, $params);
    }


    public function getLast($name, Fluent $params)
    {
        $params["__order"] = ["--id", "DESC"];
        $params["__offset"] = 0;
        $params["__limit"] = 1;
        return $this->getActive($name, $params);
    }


    public function count(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $arr = $params->toArray();

        $this->buildStructure($model, $name, $arr);

        $where = $this->getWhere($name, $arr);

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $id = $model->root->get("--id");
        $name = ($model->use_alias) ? $model->root->alias . "." . $id->name : $id->name;
        $model->select($model->raw("count(" . $name . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        return  $model->get()->first();
    }


   
}
