<?php

namespace GenerCodeOrm;

use \Illuminate\Container\Container;
use \Illuminate\Support\Fluent;

class ModelController
{
    protected $app;
    protected $repo;
    protected \GenerCodeOrm\Hooks $hooks;
    protected \GenerCodeOrm\Profile $profile;

    public function __construct(
        Container $app
    )
    { 
        $this->app = $app;
        $this->profile = $app->get(\GenerCodeOrm\Profile::class);
        $this->hooks = $app->make(\GenerCodeOrm\Hooks::class);
        $this->repo = new SchemaRepository($this->profile->factory);
    }

    private function checkPermission($name, $perm) {
        if (!$this->profile->hasPermission($name, $perm)) {
            throw new Exceptions\UserAuthException("No " . $perm . " permission for " . $name);
        }
    }

    private function trigger($name, $method, $res)
    {
        return $this->hooks->trigger($name, $method, $res);
    }

    private function handleFileUploads() {
        
    }

    private function parseParams($model, $params)
    {
        $arr = $params->toArray();
        $where = [];
        foreach ($arr as $key=>$val) {
            switch($key) {
                case '__children':
                    $model->children = $val;
                    break;
                case '__to':
                    $model->to = $val;
                    break;
                case '__order':
                    $model->order = $val;
                    break;
                case '__limit':
                    $model->limit = $val;
                    break;
                case '__offset':
                    $model->offset = $val;
                    break;
                case '__group':
                    $model->group = $val;
                    break;
                case '__fields':
                    $model->fields = $val;
                    break;
                default:
                    $where[$key] = $val;
            }
        }
        $model->where = $where;
    }

  

    public function create($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $fileHandler = $this->app->make(FileHandler::class);
        $fileHandler->init($this->repo, $name);
        $params = new Fluent(array_merge($params->toArray(), $fileHandler->uploadFiles()));

        $model= $this->app->make(Model::class);
        $model->name = $name;
        $model->data = $params->toArray();
        $res = $model->create();

        return $this->trigger($name, "post", $res);
    }



    public function update($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $fileHandler = $this->app->make(FileHandler::class);
        $fileHandler->init($this->repo, $name);
        $arr = array_merge($params->toArray(), $fileHandler->uploadFiles());
      
        $params = new Fluent($arr);

        $model= $this->app->make(Model::class);
        $model->name = $name;
        $model->where = ["--id" => $params["--id"]];
        unset($params["--id"]);

        $model->data = $params->toArray();

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $res = $model->update();

        return $this->trigger($name, "put", $res);
    }


    public function delete(string $name, Fluent $params)
    {
        $this->checkPermission($name, "delete");

        $model= $this->app->make(Model::class);

        $model->name = $name;
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model->where = ["--id", $params["--id"]];
        $res = $model->delete();

        if ($res["affected_rows"] > 0) {
            $fileHandler = $this->app->make(FileHandler::class);
            $fileHandler->init($this->repo, $name);
            $params = $fileHandler->deleteFiles($res["original_data"]);    
        }
        return $this->trigger($name, "delete", $res);
    }



    public function resort($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model= $this->app->make(Model::class);
        $model->name = $name;
        $model->data = $params;
        $model->multipleUpdate();

        return true;
    }


    public function get(string $name, Fluent $params, $state = null)
    {
        $this->checkPermission($name, "get");

        $model= $this->app->make(Repository::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        if ($state == "active") $params["__limit"] = 1;
        $this->parseParams($model, $params);
       
        $res = (isset($params["__limit"]) && $params["__limit"] == 1) ? $model->get() : $model->getAll();
        return $this->trigger($name, "get", $res);
    }


    public function getActive(string $name, Fluent $params) {
        $params["__limit"] = 1;
        $params["__order"] = null;
        $params["__group"] = null;
        return $this->get($name, $params);
    }


    
    public function getFirst($name, Fluent $params)
    {
        $params["__order"] = ["--id", "ASC"];
        $params["__ofset"] = 0;
        $params["__limit"] = 1;
        return $this->get($name, $params);
    }


    public function getLast($name, Fluent $params)
    {
        $params["__order"] = ["--id", "DESC"];
        $params["__ofset"] = 0;
        $params["__limit"] = 1;
        return $this->get($name, $params);
    }


    public function count(string $name, Fluent $params) {
        $this->checkPermission($name, "get");

        $model= $this->app->make(Repository::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $this->parseParams($model, $params);
        return $model->count();

        return $res;
    }


    public function reference(string $name, string $field, $id) {
        $this->checkPermission($name, "get");

        $cell = $this->repo_schema->get($field);
        
        $model= $this->app->make(Repository::class);
        $repo->name = $cell->reference;

        if ($cell->common) {
            if ($cell->common) {
                $parent = $this->repo_schema->has("--parent"); //must have parent
                if ($cell->common == $parent->reference) {
                    $repo->where = ["--parent"=>$id];
                }
            } else {
                $crepo = $this->app->make(Repository::class);
                $crepo->name = $this->name;
                $crepo->to = $cell->common;
                $crepo->where = ["--parent"=>$id];
                $crepo->limit = 1;
                $obj = $crepo->get();
                $repo->to = $cell->common;
                $repo->where = [$cell->common + "/--id" => $obj->{ $cell->common + "/--id"}];
            }
        }

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $repo->secure = $this->profile->id;
        }

        $this->parseParams($model, $params);
        return $repo->getAsReference();
    }

    
    public function getAsset(string $name, string $field, int $id) {
        $this->checkPermission($name, "get");

        $model= $this->app->make(Model::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $src = $model->getAsset($field, $id);


        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->get($src);

    } 


    public function removeAsset(string $name, string $field, int $id) {
        $this->checkPermission($name, "get");

        $model= $this->app->make(Model::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $src = $model->getAsset($field, $id);

        $fileHandler = $this->app->make(FileHandler::class);
        $fileHandler->init($this->repo, $name);
        return $fileHandler->delete();

    } 

}
