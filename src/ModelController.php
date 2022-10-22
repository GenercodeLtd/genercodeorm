<?php

namespace GenerCodeOrm;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class ModelController extends AppController
{
    protected function audit($id, $action, ?array $data = null)
    {
        $model = $this->model("audit");
        $data = ($data) ? json_encode($data) : "{}";
        $repo = $model->root;

        $dataSet = new DataSet($model);

        $data = [
            "model"=>$this->name,
            "model-id"=>$id,
            "action"=>$action,
            "user-login-id"=>$this->profile->id,
            "log"=>$data
        ];

        foreach ($data as $alias=>$val) {
            $bind = new Binds\SimpleBind($repo->getCell($alias), $val);
            $dataSet->addBind($bind);
        }

        $dataSet->validate("Audit");
        $model->setFromEntity()->create($dataSet->toCellNameArr());
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
        ->where($bind->cell->name, "=", $bind->value)
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

        foreach ($model->root->cells as $alias=>$cell) {
            if (get_class($cell) == Cells\AssetCell::class and isset($_FILES[$alias])) {
                $asset = new Binds\AssetBind($cell, $_FILES[$alias]);
                $asset->finalValidation("Create " . $name);
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

        $id = $model->setFromEntity()->insertGetId($data->toCellNameArr());

        if ($model->root->hasAudit()) {
            $this->audit($id, "POST");
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

        $bind = new Binds\SimpleBind($model->root->get("--id"), $params["--id"]);
        $bind->finalValidation();

        $model->filter($bind);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

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
            $changed_arr = [];
            foreach ($data->getBinds() as $alias=>$bind) {
                $changed_arr[$alias] = $original_data[$alias];
            }

            $this->audit($params["--id"], "PUT", $changed_arr);
        }

        $rows = $model->setFromEntity()->update($data->toCellNameArr());

        $result = [
            "original"=>$original_data,
            "data"=>$data->toArr(),
            "affected_rows"=>$rows
        ];

        return $this->trigger($name, "put", $result);
    }


    protected function deleteRecord(Model $model, $name, Binds\SimpleBind $id)
    {
        $id->finalValidation();

        $original_data = $this->select($name, $id->value);

        if (!$original_data) {
            return [
                "original" => new Fluent([]),
                "affected_rows" => 0
            ];
        }


        if ($model->root->hasAudit()) {
            $this->audit($id, "DELETE", $original_data);
        }

        

        $stmt = $model->stmt;
        $stmt->execute([$id->value]);

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

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
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
                $res[$id] = $this->deleteRecord($model, $name, $id);
                if ($res[$id]["affected_rows"] > 0) {
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
            $res = $this->deleteRecord($model, $name, $id);
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

        $model= $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }


        $id = new Binds\SimpleBind($model->root->get("--id"));
        $sort = new Binds\SimpleBind($model->root->get("--sort"));

        $model->filter($id);
        $model->updateStmt([$sort->cell->name => "?"]);

        $dataSet = new DataSet($model);
        $dataSet->addBind("--sort", $sort);
        $dataSet->addBind("--id", $id);

        foreach ($params["_rows"] as $row) {
            $dataSet->{"--sort"} = $row['--sort'];
            $dataSet->{"--id"} = $row["--id"];
            $dataSet->validate();
            $model->execute(array_values($dataSet->toArr()));
        }

        return true;
    }
}
