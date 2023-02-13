<?php

namespace GenerCodeOrm\Models;

use Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Builder\Builder;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;
use \GenerCodeOrm\ImportCSV;
use \Illuminate\Support\Fluent;


class Model extends App
{

    protected function audit($id, $action, ?array $data = null)
    {
        $model = $this->builder("audit");
        $data = ($data) ? json_encode($data) : "{}";
        $repo = $model->root;

        $dataSet = new DataSet($model);

        $data = [
            "model"=>$this->name,
            "model-id"=>$id,
            "action"=>$action,
            "user-login-id"=>$this->profile->id,
            "log"=>json_encode($data)
        ];

        foreach ($data as $alias=>$val) {
            $bind = new Binds\SimpleBind($repo->get($alias), $val);
            $dataSet->addBind($alias, $bind);
        }

        $dataSet->validate("Audit");
        $model->setFromEntity(true)->insert($dataSet->toCellNameArr());
    }


    protected function checkUniques(\GenerCodeOrm\DataSet $data, $id_bind = null)
    {
        $binds = $data->getBinds();
        foreach ($binds as $alias=>$bind) {
            if ($alias == "--id") {
                continue;
            }
            if ($bind->cell->unique) {
                $model = $this->builder();
                $model->select($bind->cell->name);
                $model->where($bind->cell->name, "=", $bind->value);

                if (isset($binds["--parent"])) {
                    $model->where($binds["--parent"]->cell->name, "=", $binds["--parent"]->value);
                }

                if ($id_bind) {
                    $model->where($id_bind->cell->name, "!=", $id_bind->value);
                }

                $res = $model->setFromEntity()->take(1)->get();
                if (count($res) > 0) {
                    throw new Exceptions\UniqueException($alias, $data->$alias);
                }
            }
        }
    }


    public function select($id)
    {
        $model = $this->builder();
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


    public function getNumRows() {

    }



    public function create(array $params)
    {
        $model= $this->builder();
      
        $data = new DataSet($model);

        $fileHandler = new FileHandler();

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
                $asset->validate("Create " . $this->name);
                $file_name = $fileHandler->uploadFile($asset);
                $bind = new Binds\SimpleBind($cell, $file_name);
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
        $this->checkUniques($data);

        $id = $model->setFromEntity(true)->insertGetId($data->toCellNameArr());

        if ($model->root->hasAudit()) {
            $this->audit($id, "POST", $data->toArr());
        }

        $arr = $data->toArr();
        $arr["--id"] = $id;

        return $this->trigger("post", $arr);
    }


    public function importFromCSV($params)
    {
        $model= $this->builder();
      
        $data = new DataSet($model);

        $fileHandler = new FileHandler();

        $headers = $params["headers"];

        if (!isset($_FILES['upload-csv']) OR !$_FILES['upload-csv']["size"]) {
            throw new \Exception("Must upload a csv file");
        }
        //$asset = new Binds\AssetBind($cell, $_FILES["upload-csv"]);
        //$asset->validate("Create " . $name);

        $csv = new ImportCSV($headers, $_FILES["upload-csv"]["tmp_name"]);
        

        if ($model->root->has("--owner")) {
            $bind = new Binds\SimpleBind($model->root->get("--owner"), $this->profile->id);
            $data->addBind("--parent", $bind);
        } else if ($model->root->has("--parent")) {
            $bind = new Binds\SimpleBind($model->root->get("--parent"), $params["--parent"]);
            $data->addBind("--parent", $bind);
        }

        foreach ($model->root->cells as $alias=>$cell) {
            if (!$cell->system) {
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


        $model->setFromEntity(true)->insertStmt($data->toCellNameArr());


        $rows = 0;
        $errs = 0;
        $err_details = [];
        while($arr = $csv->next()) {
            $data->apply($arr);
            try {
                $data->validate();
                $this->checkUniques($data);
                $model->execute($data);
                ++$rows;
            } catch(\Exception $e) {
                $err_details[] = [
                    "data" => $arr,
                    "err" => $e->getMessage()
                ];
                ++$errs;
            }
        }

        return ["success"=>$rows, "failure"=>$errs, "failure_details"=>$err_details];
    }



    public function update($params)
    {
        $original_data = $this->select($params["--id"]);

      
        if (!$original_data) {
            return [
                "original_data"=>null,
                "affected_rows"=>0
            ];
        }


        $model = $this->builder();

        if (!$this->profile->allowedAdminPrivilege($this->name)) {
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

        $data->validate($this->name);
        $this->checkUniques($data, $bind);
        //mayby audit here

        if ($model->root->hasAudit()) {
            $this->audit($params["--id"], "PUT", $data->toArr());
        }

        $alias = (count($model->entities) > 1) ? $model->root->alias . "." : "";
        $rows = $model->setFromEntity()->update($data->toCellNameArr($alias));

        $result = [
            "original"=>$original_data,
            "data"=>$data->toArr(),
            "affected_rows"=>$rows
        ];

        return $this->trigger("put", $result);
    }


    protected function deleteRecord(Builder $model, Binds\SimpleBind $id, $secure_id = 0)
    {
        $id->validate();

        $original_data = $this->select($id->value);

        if (!$original_data) {
            return [
                "original" => new Fluent([]),
                "affected_rows" => 0
            ];
        }


        if ($model->root->hasAudit()) {
            $odata = new Fluent($original_data);
            $this->audit($id->value, "DELETE");
        }

        

        $stmt = $model->stmt;
        if ($secure_id) $vals = [$secure_id, $id->value];
        else $vals = [$id->value];
        $stmt->execute($vals);

        $res = [
            "original"=>$original_data,
            "affected_rows"=>$stmt->rowCount()
        ];
        return $this->trigger("delete", $res);
    }


    public function delete($params)
    {
        $model= $this->builder();
        $fileHandler = new FileHandler();

        $secure_id = 0;
        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
            $secure_id = $this->profile->id;
        }

        $model->children();

        $id = new Binds\SimpleBind($model->root->get("--id"));
        $id->value = 0;
        $model->filter($id);

        $model->setFromEntity()->deleteStmt();

        //if not array, convert to array
        if (!is_array($params["--id"])) {
            $params["--id"] = [$params["--id"]];
        }

        $res = [];
        foreach ($params["--id"] as $incoming_id) {
            $id->value = $incoming_id;
            $res[$id->value] = $this->deleteRecord($model, $id,  $secure_id);
            if ($res[$id->value]["affected_rows"] > 0) {
                foreach ($model->root->cells as $alias=>$cell) {
                    if (get_class($cell) == Cells\AssetCell::class and $res[$id->value]["original"]->$alias) {
                        $fileHandler->delete($res[$id->value]["original"]->$alias);
                    }
                }
            }
        }

        if (count($res) == 0) return array_shift($res);
        else return $res;
    }



    public function resort(array $params)
    {
        if (count($params["_rows"]) == 0) return false; //nothing to do

        $model= $this->builder();

        $dataSet = new DataSet($model);

        $id = new Binds\SimpleBind($model->root->get("--id"), $params["_rows"][0]["--id"]);
        $sort = new Binds\SimpleBind($model->root->get("--sort"));

        $dataSet->addBind("--sort", $sort); //order is crucial here

        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
            //add to give the correnct number of cells.
            $cell = new Cells\IdCell();
            $bind = new Binds\SimpleBind($cell, $this->profile->id);
            $dataSet->addBind("--secure", $bind);
        }


        $model->filter($id);
        $dataSet->addBind("--id", $id);

        $model->setFromEntity();

        $alias = (count($model->entities) > 1) ? $model->root->alias . "." : "";
        $model->updateStmt([$alias . $sort->cell->name => "?"]);

        
        foreach ($params["_rows"] as $row) {
            $dataSet->{"--sort"} = $row['--sort'];
            $dataSet->{"--id"} = $row["--id"];
            $dataSet->validate();
            $model->execute($dataSet);
        }

        return true;
    }


    public function applyParams($arr) {
        foreach($arr as $key=>$val) {
            if (substr($key, 0, 2) == "__") {
                $key = substr($key, 2);
                $this->$key = $val;
            } else {
                $params[$key] = $val;
            }
        }
    }
}
