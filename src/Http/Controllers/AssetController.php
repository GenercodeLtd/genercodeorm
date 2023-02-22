<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Builder\Builder;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\DataSet;

class AssetController extends AppController
{
    //asset functions

    protected function fetchSrc(Builder $model, $name, $field, $id)
    {
        $this->checkPermission($name, "get");

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $bind = new Binds\SimpleBind($model->root->get("--id"), $id);
        $bind->validate("Fetch src");

        $model->filter($bind);

        $set = new InputSet($name);
        $set->data([$field]);
        $model->fields($set);

        $obj = $model->setFromEntity()->take(1)->get()->first();
        if ($obj) {
            return $obj->$field;
        }
    }


    protected function createSrc($name, $alias, $file, $id)
    {
        $model = $this->model($name);

        $cell = $model->root->get($alias);

        $fileHandler = app()->make(FileHandler::class);
        $asset = new Binds\AssetBind($cell, $file);
        $asset->validate("Create " . $name);
        $file_name = $fileHandler->uploadFile($asset);

        $bind = new Binds\SimpleBind($model->root->get("--id"), $id);
        $bind->validate("Create Asset");

        $model->setFromEntity()->filter($bind)->update([$cell->name => $file_name]);
        return $file_name;
    }


    public function patchAsset(string $name, string $field, int $id)
    {
        $this->checkPermission($name, "put");

        if (!isset($_FILES[$field])) {
            throw new \Exception("Must upload a file");
        }

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);
        if (!$src) {
            $src = $this->createSrc($name, $field, $_FILES[$field], $id);
        } else {
            $cell = $model->root->get($field);

            $cell->validateSize($_FILES[$field]["size"]);

            $fileHandler = app()->make(FileHandler::class);
            $fileHandler->put($src, file_get_contents($_FILES[$field]["tmp_name"]));
        }

        $this->trigger($name . "-" . $field, "patch", ["--id"=>$id, "src"=>$src]);
        return $src;
    }


    public function getAsset(string $name, string $field, int $id)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);
        $src = str_replace("%id", $id, $src);
        if ($src) {
            $fileHandler = app()->make(FileHandler::class);
            return [
                "type"=>$fileHandler->getContentType(\pathinfo($src, \PATHINFO_EXTENSION)), 
                "data"=>$fileHandler->get($src)
            ];
        } else {
            return "";
        }
    }


    public function removeAsset(string $name, string $field, int $id)
    {
        $this->checkPermission($name, "delete");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);

        $fileHandler = app()->make(FileHandler::class);
        return $fileHandler->delete($src);
    }


    public function exists(string $name, string $field, int $id) {
        $this->checkPermission($name, "get");
        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);

        if (!$src) return false;
        
        $fileHandler = app()->make(FileHandler::class);
        return $fileHandler->exists($src);
    }
}
