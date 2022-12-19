<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\DataSet;


class AuditController extends AppController
{
    //audit functions

    public function getObjectAt($name, $id, $date)
    {
        $this->checkPermission($name, "get");

        $model = $this->model("audit");


        $where = new InputSet("audit");
        $where->addData("model-id", $id);
        $where->addData("model", $name);
        $where->addData("--created", ["max"=>$date]);
      
        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $orderSet = new InputSet("audit");
        $orderSet->data(["--created"=>"ASC"]);
        $model->order($orderSet);

        $rows = $model->setFromEntity()->get()->toArray();
       
        if (count($rows) == 0) return null;

        $hist = [];
        foreach($rows as $row) {
            $log = json_decode($row->log, true);
            foreach($log as $key=>$val) {
                $hist[$key] = $val;
            }
        }
        return $hist;
    }


    public function hasChangedSince($name, $id, $date) {
        $this->checkPermission($name, "get");

        $model = $this->model("audit");

        $where = new InputSet("audit");
        $where->addData("model-id", $id);
        $where->addData("model", $name);
        $where->addData("--created", ["min"=>$date]);
      
        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $orderSet = new InputSet("audit");
        $orderSet->data(["--created"=>"ASC"]);
        $model->order($orderSet);
        $model->take(1);

        $obj = $model->setFromEntity()->first();
        return ($obj) ? true : false;
    }


    public function getAllDeletedSince($date) {
        $model = $this->model("audit");

        $where = new InputSet("audit");
        $where->addData("action", "DELETE");
        $where->addData("--created", ["min"=>$date]);

        $dataSet = new DataSet("audit");
        $dataSet->data($where);
        $dataSet->validate();

        return $model->setFromEntity()->get()->toArray();
    }


}
