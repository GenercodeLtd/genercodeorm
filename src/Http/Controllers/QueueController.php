<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use \GenerCodeOrm\Models\Repository;

class QueueController extends AppController
{

    protected function buildRepo($name, $params) {
       
        $repo->apply($params->toArray());
        return $repo;
    }
    
    public function status($id)
    {
        $repo = new Repository("queue");
        $repo->apply(["--id"=>$id, "__fields"=>["progress"]]);
        $row = $repo->getActive();
        return $row->progress;
    }

    public function remove($id) {
        $model= new Model("queue");
        $model->delete(["--id"=>$id]);
        return "success";
    }

   
}
