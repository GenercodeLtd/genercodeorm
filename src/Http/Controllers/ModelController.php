<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use GenerCodeOrm\Models\Model;


class ModelController extends AppController
{
    public function create($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $model= new Model($name);
        return $model->create($params->toArray());
    }


    public function importFromCSV($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $model= new Model($name);
        return $model->importFromCSV($params->toArray());
    }



    public function update($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $model= new Model($name);
        return $model->update($params->toArray());
    }



    public function delete(string $name, Fluent $params)
    {
        $this->checkPermission($name, "delete");

        $model= new Model($name);
        return $model->delete($params->toArray());
    }



    public function resort($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        if (count($params["_rows"]) == 0) return false; //nothing to do

        $model= new Model($name);
        return $model->resort($params->toArray());
    }
}
