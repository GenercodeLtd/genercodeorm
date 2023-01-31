<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use GenerCodeOrm\Models\Model;


class ModelController extends AppController
{
    public function create($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $model= new Model();
        $model->apply($params);
        return $model->create($name);
    }


    public function importFromCSV($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $model= new Model();
        $model->apply($params);
        return $model->importFromCSV($name);
    }



    public function update($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $model= new Model();
        $model->apply($params);
        return $model->update($name);
    }



    public function delete(string $name, Fluent $params)
    {
        $this->checkPermission($name, "delete");

        $model= new Model();
        $model->apply($params);
        return $model->delete($name);
    }



    public function resort($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        if (count($params["_rows"]) == 0) return false; //nothing to do

        $model= new Model();
        $model->apply($params);
        return $model->resort($name);
    }
}
