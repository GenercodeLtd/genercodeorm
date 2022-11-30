<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\InputSet;


class AuditController extends AppController
{
    //audit functions

    public function getObjectAt($name, $id, $last_published)
    {
        $this->checkPermission($name, "get");

        $model = $this->model("audit");


        $where = new InputSet("audit");
        $where->addData("model-id", $id);
        $where->addData("model", $name);
        $where->addData("--created", ["min"=>$last_published]);
      
        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $orderSet = new InputSet("audit");
        $orderSet->data(["--created"=>"ASC"]);
        $model->order($orderSet);

        $vals = $model->setFromEntity()->get()->toArray();
       
        if (count($vals) == 0) return null;

        if ($vals[count($vals) - 1]->action == "POST") return null;

        $vals = array_reverse($vals);

        $hist = [];
        foreach($vals as $row) {
            $log = json_decode($row->log);
            foreach ($log as $key=>$val) {
                $hist[$key] = $val;
            }
        }
        return $hist;
    }


}
