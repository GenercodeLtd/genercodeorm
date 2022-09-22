<?php

namespace GenerCodeOrm;

class ModelController
{
    protected $profile;
    protected $dbmanager;
    protected $factory;
    protected $hooks;

    public function __construct(
        \Illuminate\Database\DatabaseManager $dbmanager,
        SchemaFactory $factory,
        Profile $profile,
        Hooks $hooks
    )
    {
        $this->dbmanager = $dbmanager;
        $this->factory = $factory;
        $this->profile = $profile;
        $this->hooks = $hooks;
    }

    private function trigger($name, $method, $res)
    {
        if ($this->hooks->has($name, $method)) {
            $res = $this->hooks->trigger($name, $method, $res);
        }
        return $res;
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
        if (!$this->profile->hasPermission($name, "post")) {
            throw new Exceptions\UserAuthException("No permission to post " . $name);
        }

        $model = new Model($this->dbmanager, $this->factory);
        $model->name = $name;
        $model->data = $params;
        $res = $model->create();

        return $this->trigger($name, "post", $res);
    }



    public function update($name, array $params)
    {
        if (!$this->profile->hasPermission($name, "put")) {
            throw new Exceptions\UserAuthException("No permission to put " . $name);
        }

        $model= new Model($this->dbmanager, $this->factory);
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
        if (!$this->profile->hasPermission($name, "delete")) {
            throw new Exceptions\UserAuthException("No permission to delete " . $name);
        }

        $model= new Model($this->dbmanager, $this->factory);

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
        if (!$this->profile->hasPermission($name, "put")) {
            throw new Exceptions\UserAuthException("No permission to resort " . $name);
        }

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model = new Model($this->dbmanager, $this->factory);
        $model->name = $name;
        $where = ["--id"=>[]];
        $data = ["--sort"=>[]];
        foreach($params as $row) {
            $where["--id"][] = $row["--id"]; 
            $data["--sort"][] = $row["--sort"];
        }
        $model->data = $data;
        $model->where = $where;
        $model->multipleUpdate();

        return true;
    }


    public function get(string $name, array $params)
    {
        if (!$this->profile->hasPermission($name, "get")) {
            throw new Exceptions\UserAuthException("No permission to get " . $name);
        }

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure = $this->profile->id;
        }

        $model = new Model($this->dbmanager, $this->factory);
        $model->name = $name;
        $this->parseParams($model, $params);
        $res = $model->get();

        $res = ($params["__limit"] == 1) ? $res->first() : $res->toArray();
        return $this->trigger($name, "get", $res);
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
        if (!$this->profile->hasPermission($name, "get")) {
            throw new Exceptions\UserAuthException("No permission to get " . $name);
        }
    }


    public function reference(string $name, string $field, $id) {
        if (!$this->profile->hasPermission($name, "get")) {
            throw new Exceptions\UserAuthException("No permission to get " . $name);
        }
    }

}
