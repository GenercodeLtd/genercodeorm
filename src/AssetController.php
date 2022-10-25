<?php

namespace GenerCodeOrm;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class AssetController extends AppController
{
    //asset functions

    protected function fetchSrc(Model $model, $name, $field, $id)
    {
        $this->checkPermission($name, "get");

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $bind = new Binds\SimpleBind($model->root->get("--id"), $id);
        $bind->validate("Fetch src");

        $model->filter($bind);
        $model->fields([$field]);

        $obj = $model->setFromEntity()->take(1)->get()->first();
        if ($obj) {
            return $obj->$field;
        }
    }


    protected function createSrc($name, $alias, $file, $id)
    {
        $nmodel = $this->model($name);

        $cell = $model->root->get($alias);

        $fileHandler = $this->app->make(FileHandler::class);
        $asset = new Binds\AssetBind($cell, $file);
        $asset->validate("Create " . $name);
        $file_name = $fileHandler->uploadFile($asset);

        $bind = new Binds\SetBind($model->root->get("--id"), $id);
        $bind->validate("Create Asset");

        $model->setFromEntity()->filter($bind)->update([$cell->name => $file_name]);
    }


    public function patchAsset(string $name, string $field, int $id, $body)
    {
        $this->checkPermission($name, "put");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $name);
        if (!$src) {
            if (isset($_FILES[$field])) {
                $this->createSrc($name, $field, $_FILES[$field]);
                return true;
            }
            return false;
        } else {
            $cell = $model->getCell($name);

            $cell->validateSize(strlen($body));

            $fileHandler = $this->app->make(FileHandler::class);
            return $fileHandler->put($src, $body);
        }
    }


    public function getAsset(string $name, string $field, int $id)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        $src = $this->fetchSrc($model, $name, $field, $id);
        if ($src) {
            $fileHandler = $this->app->make(FileHandler::class);
            return $fileHandler->get($src);
        } else {
            return "";
        }
    }


    public function removeAsset(string $name, string $field, int $id)
    {
        $this->checkPermission($name, "get");

        $model= $this->app->makeWith(Model::class, ["name"=>$name, "factory"=>$this->profile->factory]);

        $src = $this->fetchSrc($model, $name, $field, $id);

        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->delete($src);
    }
}
