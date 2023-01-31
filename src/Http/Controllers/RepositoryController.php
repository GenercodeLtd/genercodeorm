<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use \GenerCodeOrm\Models\Repository;

class RepositoryController extends AppController
{
    
    public function get(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = new Repository();
        $repo->apply($params->toArray());
        return $repo->get($name);
    }


    public function getActive(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = new Repository();
        $repo->apply($params->toArray());
        return $repo->getActive($name);
    }



    public function getFirst($name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = new Repository();
        $repo->apply($params->toArray());
        return $repo->getFirst($name);
    }


    public function getLast($name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = new Repository();
        $repo->apply($params->toArray());
        return $repo->getLast($name);
    }


    public function count(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = new Repository();
        $repo->apply($params->toArray());
        return $repo->count($name);
    }


   
}
