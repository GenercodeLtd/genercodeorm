<?php

namespace GenerCodeOrm;

class ModelController
{
    protected $repo;
    protected $dbmanager;
    protected $hooks;
    protected $profile;

    public function __construct(
        \Illuminate\Database\DatabaseManager $dbmanager,
        Profile $profile,
        Hooks $hooks
    )
    {
        $this->dbmanager = $dbmanager;
        $this->profile = $profile;
        $this->hooks = $hooks;
        $this->repo = new SchemaRepository($this->profile->factory);
    }

    private function checkPermission($name, $perm) {
        if (!$this->profile->hasPermission($name, $perm)) {
            throw new Exceptions\UserAuthException("No " . $perm . " permission for " . $name);
        }
    }

    private function trigger($name, $method, $res)
    {
        if ($this->hooks->has($name, $method)) {
            $res = $this->hooks->trigger($name, $method, $res);
        }
        return $res;
    }

    private function handleFileUploads() {
        
    }

    private function parseParams($model, $params)
    {
        $where = [];
        foreach ($params as $key=>$val) {
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


    public function create($name, array $params)
    {
        $this->checkPermission($name, "post");

        $model = new Model($this->dbmanager, $this->repo);
        $model->name = $name;
        $model->data = $params;
        $res = $model->create();

        return $this->trigger($name, "post", $res);
    }



    public function update($name, array $params)
    {
        $this->checkPermission($name, "put");

        $model= new Model($this->dbmanager, $this->repo);
        $model->name = $name;
        $model->where = ["--id", $params["--id"]];
        $model->data = $params;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $res = $model->update();

        return $this->trigger($name, "put", $res);
    }


    public function delete(string $name, array $params)
    {
        $this->checkPermission($name, "delete");

        $model= new Model($this->dbmanager, $this->repo);

        $model->name = $name;
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model->where = ["--id", $params["--id"]];
        $res = $model->delete();

        return $this->trigger($name, "delete", $res);
    }



    public function resort($name, array $params)
    {
        $this->checkPermission($name, "put");

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model = new Model($this->dbmanager, $this->repo);
        $model->name = $name;
        $model->data = $params;
        $model->multipleUpdate();

        return true;
    }


    public function get(string $name, array $params)
    {
        $this->checkPermission($name, "get");

        $model = new Repository($this->dbmanager, $this->repo);
        $model->name = $name;

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $this->parseParams($model, $params);
        $res = $model->get();

        $res = ($params["__limit"] == 1) ? $res->first() : $res->toArray();
        return $this->trigger($name, "get", $res);
    }


    public function getActive(string $name, array $params) {
        $params["__limit"] = 1;
        $params["__order"] = null;
        $params["__group"] = null;
        return $this->get($name, $params);
    }


    
    public function getFirst($name, array $params)
    {
        $params["__order"] = ["--id", "ASC"];
        $params["__ofset"] = 0;
        $params["__limit"] = 1;
        return $this->get($name, $params);
    }


    public function getLast($name, array $params)
    {
        $params["__order"] = ["--id", "DESC"];
        $params["__ofset"] = 0;
        $params["__limit"] = 1;
        return $this->get($name, $params);
    }




    public function count(string $name, array $params) {
        $this->checkPermission($name, "get");

        $model = new Repository($this->dbmanager, $this->repo);
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
        
        $repo = new Repository($this->dbmanager, $this->repo);
        $repo->name = $cell->reference;

        if ($cell->common) {
            if ($cell->common) {
                $parent = $this->repo_schema->has("--parent"); //must have parent
                if ($cell->common == $parent->reference) {
                    $repo->where = ["--parent"=>$id];
                }
            } else {
                $crepo = new Repository($this->dbmanager, $this->repo);
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

}
