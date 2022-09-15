<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use GenercodeCore\Facades\Schema;

class Model
{
    protected $name;
    protected $profile;
    protected $schema;

    public function __construct($profile, $name)
    {
        $this->query = $query;
        $this->profile = $profile;
        $this->name = $name;
        $this->schema = Schema::get($name);
    }


    private function archive($id)
    {
        $data = new DataSet();
        $archive_data = new DataSet();

        $aliases = $this->schema->getAllAliases();
        $aliases = array_filter($aliases, function ($val) {
            return ($val == "--created" or $val == "--updated") ? false : true;
        });

        foreach ($aliases as $alias) {
            $cell = $this->schema->get($alias);
            $data->bind($alias, $cell);
            $archive_data->bind($alias, $cell);
        }

        $data->{"--id"} = $id;
        $data->validate();

        $archive_data->bind("--archive", $this->schema->getArchive());

        $map = new Mappers()/MapCopy($this->query);
        $map->copy($data, $archive_data);
    }


    public function create(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "post")) {
            throw \Exception();
        }

        $data = new DataSet();

        if ($this->schema->hasParent()) {
            $data->bind("--parent", $this->schema->get("--parent"));
        }

        $aliases = $this->schema->getAllAliases();
        foreach ($aliases as $alias) {
            $cell = $this->schema->get($alias);
            if (!$cell->system and ($cell->required or isset($params[$alias]))) {
                $data->bind($alias, $cell);
            }
        }

        $data->apply($params);
        $data->validate();

        $save = new MapCrud($query);
        $data->bind("--id", $this->schema->get("--id"));
        $data->{"--id"} = $save->post($model);

        trigger($this->name, "create", $data);
        return $data->toArr();
    }


    public function update(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "put")) {
            throw \Exception();
        }

        $data = new DataSet();
        $data->bind("--id", $this->schema->get("--id"));

        foreach ($params as $key=>$val) {
            $cell = $this->schema->get($key);
            if (!$cell->system) {
                $data->bind($key, $cell);
                $data->$key = $val;
            }
        }

        $data->validate();


        $crud = new MapCrud($query);
        $original_data = $crud->select($schema, $model);

        if ($schema->archive) {
            $this->archive($data->{"--id"});
        }
        $crud->update($schema, $model);

        trigger($this->name, "update", $model->asCollection(), $original_data);

        return true;
    }


    public function delete(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "delete")) {
            throw \Exception();
        }

        $data = new DataSet();
        $data->bind("--id", $this->schema->get("--id"));
        $data->{"--id"} = $params["--id"];

        $data->validate();

        if ($schema->archive) {
            $this->archive($data->{"--id"});
        }

        $crud = new MapCrud();
        $original_data = $crud->select($schema, $model);

        $crud->delete($schema, $model);

        trigger($this->name, "delete", $model->asCollection(), $original_data);
        $response->body->write(json_encode("success"));
    }



    public function resort(array $params)
    {
        if (!$this->profile->hasPermission($this->name, "put")) {
            throw Exception();
        }

        $schema = $this->loadSchema($this->name);

        $sortCol = $collection->getFromAlias("--sort");
        $idCol = $collection->getFromAlias("--id");

        $schema->addActiveCell("--id", $idCol);
        $schema->addActiveCell("--sort", $sortCol);

        $models = [];
        foreach ($params as $row) {
            $model = new Model($row);
            Validator::validate($schema, $model);
            $models[] = $model;
        }



        $sql = "UPDATE " . $collection->table . " SET " . $sortCol->name . " = ? WHERE " . $idCol->name . " = ?";

        $stmt = new MapPrepared($query);
        $stmt->prepare($sql);

        foreach ($models as $model) {
            $stmt->execute($model);
        }

        return true;
    }
}
