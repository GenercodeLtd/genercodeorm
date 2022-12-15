<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\InputSet;


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
            foreach($row as $key=>$val) {
                $hist[$key] = $val;
            }
        }
        return $hist;
    }


}
