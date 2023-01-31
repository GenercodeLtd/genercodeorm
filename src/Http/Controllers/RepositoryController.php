<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use \GenerCodeOrm\Models\Repository;

class RepositoryController extends AppController
{

    protected function buildRepo($name, $params) {
        $repo = new Repository($this->app->make(\GenerCodeOrm\Hooks::class), $this->app->get("profile"), $name);
        $repo->apply($params->toArray());
        return $repo;
    }
    
    public function get(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = $this->buildRepo($name, $params);
        return $repo->get();
    }


    public function getActive(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = $this->buildRepo($name, $params);
        return $repo->getActive();
    }



    public function getFirst($name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = $this->buildRepo($name, $params);
        return $repo->getFirst();
    }


    public function getLast($name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = $this->buildRepo($name, $params);
        return $repo->getLast();
    }


    public function count(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $repo = $this->buildRepo($name, $params);
        return $repo->count();
    }


   
}
