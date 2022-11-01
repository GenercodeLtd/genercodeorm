<?php

namespace GenerCodeOrm;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class AuditController extends AppController
{
    //audit functions


    public function getObjectAt($name, $id, $last_published)
    {
        $this->checkPermission($name, "get");

        $ctrl = $this->app->get(ModelController::class);
        $vals = $ctrl->get("audit", new Fluent([
            "--created" => [ "min"=>$last_published],
            "model"=>$name,
            "model-id"=>$id,
            "__order"=>["--created"=>"DESC"]]));

        if (count($vals) == 0) return new \StdClass;

        if ($vals[count($vals - 1)]->action == "POST") return new \StdClass;

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
