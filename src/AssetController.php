<?php

namespace GenerCodeOrm;

use \Illuminate\Container\Container;
use \Illuminate\Support\Fluent;

class AssetController extends AppController
{

    //asset functions

    protected function fetchSrc(Model $model, $name, $field, $id) {
        $this->checkPermission($name, "get");
       
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $model->select($name);

        $cell = $model->root->getCell("--id");
        $bind = new Binds\SimpleBind($cell, $id);
        $bind->validate();

        $obj = $model->setFromEntity()->take(1)->get();
        if ($obj) {
            return $obj->$name;
        }
    }


    public function patchAsset(string $name, string $field, int $id, $body) {
        $this->checkPermission($name, "put");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $name);
        $cell = $model->getCell($name);

        $cell->validateSize(strlen($body));

        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->put($src, $body);

    } 

    
    public function getAsset(string $name, string $field, int $id) {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);

        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->get($src);

    } 


    public function removeAsset(string $name, string $field, int $id) {
        $this->checkPermission($name, "get");

        $model= $this->app->makeWith(Model::class, ["name"=>$name, "factory"=>$this->profile->factory]);

        $src = $this->fetchSrc($model, $name, $field, $id);

        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->delete($src);

    } 

}