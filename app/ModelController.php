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
        $this->repo = $app->get(\GenerCodeOrm\EntityManager::class);
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
                    $model->order =  (!is_string($val)) ? $val : json_decode($val, true);
                    break;
                case '__limit':
                    if (strpos($val, ",") !== false) {
                        $pts = explode(",", $val);
                        $model->offset = $pts[0];
                        $model->limit = $pts[1];
                    } else {
                        $model->limit = $val;
                    }
                    break;
                case '__offset':
                    $model->offset = $val;
                    break;
                case '__group':
                    $model->group = $val;
                    break;
                case '__fields':
                    $model->fields = (!is_string($val)) ? $val : json_decode($val, true);
                    break;
                default:
                    $where[$key] = $val;
            }
        }
        $model->where = $where;
    }


    protected function audit($id, $action, ?array $data = null)
    {
        $model = $this->app->make(Model::class, "audit");
        $data = ($data) ? json_encode($data) : "{}";

        $dataSet = new DataSet();
        
        $data = [
            "model"=>$this->name, 
            "model-id"=>$id,
            "action"=>$action, 
            "user-login-id"=>$this->secure,
            "log"=>$data
        ];

        foreach($data as $alias=>$val) {
            $bind = new Binds\SimpleBind($repo->getCell($alias), $val);
            $dataSet->addBind($bind);
        }

        $dataSet->validate("Audit");
        $model->create($dataSet);
    }


    protected function checkUniques(\GenerCodeOrm\DataSet $data)
    {
        
        $id_cell = $root->get("--id");

        $binds = $data->getBinds();
        foreach ($binds as $alias=>$bind) {
            if ($bind->cell->unique) {
                $repo = new \GenerCodeOrm\EntityManager($this->en_manager->getFactory());
                $model = $this->app->makeWith(Model::class, ["name"=>$this->name]);

                $model->select($bind->cell->name);

                $model->where($bind->cell->name, "=", $bind->value);
               

                if (isset($binds["--parent"])) {
                    $model->where("--parent", "=", $binds["--parent"]->value);
                }

                
                if (isset($binds["--id"])) {
                    $model->where("--id", "!=", $binds["--id"]->value);
                }

        
                $res = $model->take(1)->get();
                if (count($res) > 0) {
                    throw new Exceptions\UniqueException($alias, $data->$alias);
                }
            }
        }
    }

  

    public function create($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $this->repo->loadBase($name);
        $schema = $this->repo->getSchema("");
        $fcells = [];

        $data = new DataSet();

        foreach($schema->cells as $alias=>$cell) {
            if (get_class($cell) == Cells\AssetCell::class AND isset($_FILES[$alias])) {
                $fcells[$alias] = $cell;
            }

            if (!$cell->system and ($cell->required or isset($this->data[$alias]))) {
                $bind = new Binds\SimpleBind($cell);
                $data->addBind($alias, $bind);
            } else if ($alias == "--owner" AND $this->secure) {
                $bind = new Binds\SimpleBind($cell, $this->secure);
                $data->addBind($alias, $bind);
            }
        }


        if (count($fcells) > 0) {
            $fileHandler = $this->app->make(FileHandler::class);
            $file_names = $fileHandler->uploadFiles($fcells);
            foreach ($file_names as $alias=>$src) {
                $params[$alias] = $src;
            }
        }
        
        $data->validate();
        $this->checkUniques($data);
        
        $model= $this->app->makeWith(Model::class, ["name"=>$name]);
        //$model->secure($this->profile->id);
        $res = $model->create($data);

        return $this->trigger($name, "post", $res);
    }



    public function update($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $original_data = $this->select($params["--id"]);

        

        $schema = $this->en_manager->getSchema("");
        $bind = new Binds\SimpleBind($schema->get("--id"), $params["--id"]);
        $bind->validate();

        $model = $this->app->makeWith(Model::class, ["name"=>$name]);
        $model->where($bind->cell->entity->alias . "." . $bind->cell->name, "=", $bind->cell->value);


        $data = new DataSet();

        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and isset($this->data[$alias]) AND !$cell->immutable) {
                $bind = new Binds\SimpleBind($cell, $params[$alias]);
                $data->addBind($alias, $bind);
            }
        }

        $data->validate($name);
        $this->checkUniques();
        //mayby audit here

        $res = $model->update($data->toCellArr());


        $where_data = $this->createDataSet($this->where);
      
        $original_data = $this->select($where_data);

        if (!$original_data) {
            return [
                "original_data"=>null,
                "affected_rows"=>0
            ];
        }

        $original_data = new Fluent($original_data);

        $data = new DataSet();
        
        

        $data->apply($this->data);
        $data->validate();

        $this->checkUniques($data);

        if ($schema->hasAudit()) {
            $changed_arr = [];
            foreach($data->getBinds() as $alias=>$bind) {
                $changed_arr[$alias] = $original_data[$alias];
            }

            $this->audit($where_data->{"--id"}, "PUT", $changed_arr);
        }

        $root = $this->en_manager->getSchema("");
        $query = $this->buildQuery($root->table, $root->alias);
        if ($this->secure) $this->secureQuery($query);
        $query->filter($where_data);

        $rows = $query->update($data->toCellNameArr($root->alias . "."));

        return [
            "original"=>$original_data,
            "data"=>$data->toArr(),
            "affected_rows"=>$rows
        ];


        $model= $this->app->makeWith(Model::class, ["name"=>$name]);
        $model->where("--id", "=", $params["--id"]);
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
       
        $runDel = function($model, $fileHandler) {
            $res = $model->delete();
      
            if ($res["affected_rows"] > 0) {
                $fcells = [];
                $schema = $model->repo_schema->getSchema("");
                foreach($schema->cells as $alias=>$cell) {
                     if (get_class($cell) == Cells\AssetCell::class AND $res["original"]->{$alias}) {
                        $fileHandler->delete($res["original"]->{$alias});
                    }
                }
            }
            return $res;
        };


        $this->checkPermission($name, "delete");

        $model= $this->app->make(Model::class);
        $fileHandler = $this->app->make(FileHandler::class);

        $model->name = $name;
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        if (is_array($params["--id"])) {
            $res = [];
            foreach($params['--id'] as $id) {
                $model->where = ["--id"=>$id];
                $res[$id] = $runDel($model, $fileHandler);
            }
        } else {
            $model->where = ["--id" => $params["--id"]];
            $res = $runDel($model, $fileHandler);
        }
      
        return $this->trigger($name, "delete", $res);
    }



    public function resort($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $model= $this->app->make(Model::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

    
        $model->data = $params["_rows"];
        $model->resort();

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


    public function reference(string $name, string $field, $id, ?Fluent $params) {
        $this->checkPermission($name, "get");

        $ref = $this->app->make(Reference::class);

        $model= $this->app->make(Repository::class);
        $ref->setRepo($name, $field, $id, $model);

        if ($params) $this->parseParams($model, $params);
        return $model->getAsReference();
    }


    public function patchAsset(string $name, string $field, int $id, $body) {
        $this->checkPermission($name, "put");

        $model= $this->app->make(Model::class);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $src = $model->getAsset($field, $id);

        $repo = $model->repo_schema;
        $field = $repo->get($field);
        $field->validateSize(strlen($body));

        $fileHandler = $this->app->make(FileHandler::class);
        return $fileHandler->put($src, $body);

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
