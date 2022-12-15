<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Model;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;


class ModelController extends AppController
{
    protected function audit($name, $id, $action, ?array $data = null)
    {
        $model = $this->model("audit");
        $data = ($data) ? json_encode($data) : "{}";
        $repo = $model->root;

        $dataSet = new DataSet($model);

        $data = [
            "model"=>$name,
            "model-id"=>$id,
            "action"=>$action,
            "user-login-id"=>$this->profile->id,
            "log"=>$data
        ];

        foreach ($data as $alias=>$val) {
            $bind = new Binds\SimpleBind($repo->get($alias), $val);
            $dataSet->addBind($alias, $bind);
        }

        $dataSet->validate("Audit");
        $model->setFromEntity(true)->insert($dataSet->toCellNameArr());
    }


    protected function checkUniques($name, \GenerCodeOrm\DataSet $data)
    {
        $binds = $data->getBinds();
        foreach ($binds as $alias=>$bind) {
            if ($alias == "--id") {
                continue;
            }
            if ($bind->cell->unique) {
                $model = $this->model($name);
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


    public function select($name, $id)
    {
        $this->checkPermission($name, "get");

        $model = $this->model($name);
        $bind = new Binds\SimpleBind($model->root->get("--id"), $id);
        $bind->validate();

        return $model
        ->setFromEntity()
        ->fields()
        ->filter($bind)
        ->take(1)
        ->get()
        ->first();
    }



    public function create($name, Fluent $params)
    {
        $this->checkPermission($name, "post");

        $model= $this->model($name);
      
        $data = new DataSet($model);

        $fileHandler = $this->app->make(FileHandler::class);

        if ($model->root->has("--owner")) {
            $bind = new Binds\SimpleBind($model->root->get("--owner"), $this->profile->id);
            $data->addBind("--parent", $bind);
        } else if ($model->root->has("--parent")) {
            $bind = new Binds\SimpleBind($model->root->get("--parent"), $params["--parent"]);
            $data->addBind("--parent", $bind);
        }

        foreach ($model->root->cells as $alias=>$cell) {
            if (get_class($cell) == Cells\AssetCell::class and isset($_FILES[$alias])) {
                $asset = new Binds\AssetBind($cell, $_FILES[$alias]);
                $asset->validate("Create " . $name);
                $name = $fileHandler->uploadFile($asset);
                $bind = new Binds\SimpleBind($cell, $name);
                $data->addBind($alias, $bind);
            } else if (!$cell->system and ($cell->required or isset($params[$alias]))) {
                $bind = new Binds\SimpleBind($cell);
                if (isset($params[$alias])) {
                    $bind->value = $params[$alias];
                }
                $data->addBind($alias, $bind);
            } elseif (strpos($alias, "--owner") and $this->secure) {
                $bind = new Binds\SimpleBind($cell, $this->secure);
                $data->addBind($alias, $bind);
            }
        }



        $data->validate();
        $this->checkUniques($name, $data);

        $id = $model->setFromEntity(true)->insertGetId($data->toCellNameArr());

        if ($model->root->hasAudit()) {
            $this->audit($name, $id, "POST", $data->toArr());
        }

        $arr = $data->toArr();
        $arr["--id"] = $id;

        return $this->trigger($name, "post", $arr);
    }



    public function update($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        $original_data = $this->select($name, $params["--id"]);

      
        if (!$original_data) {
            return [
                "original_data"=>null,
                "affected_rows"=>0
            ];
        }


        $model = $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $bind = new Binds\SimpleBind($model->root->get("--id"), $params["--id"]);
        $bind->validate();

        $model->filter($bind);

        

        $data = new DataSet($model);

        foreach ($model->root->cells as $alias=>$cell) {
            if (!$cell->system and isset($params[$alias]) and !$cell->immutable) {
                $bind = new Binds\SimpleBind($cell);
                if (isset($params[$alias])) {
                    $bind->value = $params[$alias];
                }
                $data->addBind($alias, $bind);
            }
        }

        $data->validate($name);
        $this->checkUniques($name, $data);
        //mayby audit here

        if ($model->root->hasAudit()) {
            $this->audit($name, $params["--id"], "PUT", $data->toArr());
        }

        $alias = (count($model->entities) > 1) ? $model->root->alias . "." : "";
        $rows = $model->setFromEntity()->update($data->toCellNameArr($alias));

        $result = [
            "original"=>$original_data,
            "data"=>$data->toArr(),
            "affected_rows"=>$rows
        ];

        return $this->trigger($name, "put", $result);
    }


    protected function deleteRecord(Model $model, $name, Binds\SimpleBind $id, $secure_id = 0)
    {
        $id->validate();

        $original_data = $this->select($name, $id->value);

        if (!$original_data) {
            return [
                "original" => new Fluent([]),
                "affected_rows" => 0
            ];
        }


        if ($model->root->hasAudit()) {
            $odata = new Fluent($original_data);
            $this->audit($name, $id->value, "DELETE";
        }

        

        $stmt = $model->stmt;
        if ($secure_id) $vals = [$secure_id, $id->value];
        else $vals = [$id->value];
        $stmt->execute($vals);

        $res = [
            "original"=>$original_data,
            "affected_rows"=>$stmt->rowCount()
        ];
        return $this->trigger($name, "delete", $res);
    }


    public function delete(string $name, Fluent $params)
    {
        $this->checkPermission($name, "delete");

        $model= $this->model($name);
        $fileHandler = $this->app->make(FileHandler::class);

        $secure_id = 0;
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
            $secure_id = $this->profile->id;
        }

        $model->children();

        $id = new Binds\SimpleBind($model->root->get("--id"));
        $id->value = 0;
        $model->filter($id);

        $model->setFromEntity()->deleteStmt();


        if (is_array($params["--id"])) {
            $res = [];
            foreach ($params["--id"] as $incoming_id) {
                $id->value = $incoming_id;
                $res[$id->value] = $this->deleteRecord($model, $name, $id,  $secure_id);
                if ($res[$id->value]["affected_rows"] > 0) {
                    foreach ($model->root->cells as $alias=>$cell) {
                        if (get_class($cell) == Cells\AssetCell::class and $res[$id]["original"]->$alias) {
                            $fileHandler->delete($res[$id]["original"]->$alias);
                        }
                    }
                }
            }
            return $res;
        } else {
            $id->value = $params["--id"];
            $res = $this->deleteRecord($model, $name, $id, $secure_id);
            if ($res["affected_rows"] > 0) {
                foreach ($model->root->cells as $alias=>$cell) {
                    if (get_class($cell) == Cells\AssetCell::class and $res["original"]->$alias) {
                        $fileHandler->delete($res["original"]->$alias);
                    }
                }
            }
            return $res;
        }
    }



    public function resort($name, Fluent $params)
    {
        $this->checkPermission($name, "put");

        if (count($params["_rows"]) == 0) return false; //nothing to do

        $model= $this->model($name);

        $dataSet = new DataSet($model);

        $id = new Binds\SimpleBind($model->root->get("--id"), $params["_rows"][0]["--id"]);
        $sort = new Binds\SimpleBind($model->root->get("--sort"));

        $dataSet->addBind("--sort", $sort); //order is crucial here

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
            //add to give the correnct number of cells.
            $cell = new Cells\IdCell();
            $bind = new Binds\SimpleBind($cell, $this->profile->id);
            $dataSet->addBind("--secure", $bind);
        }


        $model->filter($id);
        $dataSet->addBind("--id", $id);

        $model->setFromEntity();
        $model->updateStmt([$sort->cell->name => "?"]);

        
        foreach ($params["_rows"] as $row) {
            $dataSet->{"--sort"} = $row['--sort'];
            $dataSet->{"--id"} = $row["--id"];
            $dataSet->validate();
            $model->execute($dataSet);
        }

        return true;
    }
}
