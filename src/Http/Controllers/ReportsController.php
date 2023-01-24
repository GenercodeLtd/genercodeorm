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

        $aggregates = null;
        if (isset($arr["__agg"])) {
            $set = new InputSet($name);
            $set->data($arr["__agg"]);
            $aggregates = $set->getValues();
        }

        if (isset($arr["__fields"])) {
            $set = new InputSet($name);
            $set->data($arr["__fields"]);
            $model->fields($set, $aggregates);
        } else {
            $model->fields($aggregates);
        }

        
    }


    private function getWhere($name, array $params): InputSet
    {
        $group = (isset($params["__group"])) ? $params["__group"] : [];
        $where = [];
        $set = new InputSet($name);
        foreach ($params as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                if (!in_array($key, $group)) $set->addData($key, $val);
            }
        }
        return $set;
    }

    private function getHaving($name, array $params): InputSet
    {
        $group = (isset($params["__group"])) ? $params["__group"] : [];
        $where = [];
        $set = new InputSet($name);
        foreach ($params as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                if (in_array($key, $group)) $set->addData($key, $val);
            }
        }
        return $set;
    }


    private function setParams($model, array $params) {
        $where = $this->getWhere($model->name, $params);

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $having = $this->getHaving($model->name, $params);
        $dataSet = new DataSet($model);
        $dataSet->data($having);
        $dataSet->validate();
        $model->having($dataSet);


        if (isset($params["__order"])) {
            $orderSet = new InputSet($model->name);
            $orderSet->data($params["__order"]);
            $model->order($orderSet);
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

        $this->setParams($model, $arr);
        

        $id = $model->root->get("--id");
        $res = $model->setFromEntity()
        ->select($model->raw("count(" . $id->getDBAlias() . ") as 'count'"))
        ->setFromEntity()
        ->get()
        ->toArray();
        return $this->trigger($name, "report-get", $res);
    }


    public function getAggregate(string $name, string $field, string $agg, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        $arr = $params->toArray();
        $this->buildStructure($model, $name, $arr);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $this->setParams($model, $arr);

        $res = $model->setFromEntity()->get()->toArray();
        return $this->trigger($name, "report-get", $res);
    }

   
}
