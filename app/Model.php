<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;



class Model
{
    protected $profile;
    protected $repo_schema;
    protected $dbmanager;

    public function __construct(
        \Illuminate\Database\DatabaseManager $dbmanager, 
        SchemaFactory $factory, 
        Profile $profile)
    {
        $this->dbmanager = $dbmanager;
        $this->profile = $profile;
        $this->repo_schema = new SchemaRepository($factory);
    }

    

    private function archive($idata)
    {
        $schema = $this->repo_schema->getSchema("");
        $data = new DataSet();
     
        $cols = [];
        foreach($data as $key=>$val) {
            if ($key == "date_created" OR $key == "last_updated") continue;
            $cols[$key] = $val;
        }

       // $data->apply($idata);
       // $data->validate();

        $map = new Mappers\MapQuery($this->dbmanager, $this->repo_schema);
        $map->archive($cols);
    }


    public function create($name, array $params)
    {
        if (!$this->profile->hasPermission($name, "post")) {
            throw new \Exception("No permission for " . $name . " and post");
        }

        $data = new DataSet();

        $this->repo_schema->loadBase($name);

        if ($this->repo_schema->has("--parent")) {
            $data->bind("--parent", $this->repo_schema->get("--parent"));
        }
       
        $schema = $this->repo_schema->getSchema("");
        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and ($cell->required or isset($params[$alias]))) {
                $data->bind($alias, $cell);
            }
        }

        $data->apply($params);
        $data->validate();
       
        $save = new Mappers\MapQuery($this->dbmanager, $this->repo_schema);

        $data->bind("--id", $schema->get("--id"));
        $data->{"--id"} = $save->post($data);

        return $data->toArr();
    }


    public function update($name, array $params)
    {
        if (!$this->profile->hasPermission($name, "put")) {
            throw new \Exception("this profile does not have permission to access " . $name . " and put ");
        }

        $this->repo_schema->loadBase($name);
        $schema = $this->repo_schema->getSchema("");

        $sdata = new DataSet();
        $sdata->bind("--id", $schema->get("--id"));
        $sdata->{"--id"} = $params["--id"];
        $sdata->validate();

        $crud = new Mappers\MapQuery($this->dbmanager, $this->repo_schema);
        $original_data = $crud->select($sdata)->first();


        $data = new DataSet();
        $data->bind("--id", $schema->get("--id"));

        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and isset($params[$alias])) {
                $data->bind($alias, $cell);
            }
        }

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $this->repo_schema->loadToSecure();
            $top = $this->repo_schema->getTop();
            $data->bind("--owner", $top->get("--owner"));
            $data->{"--owner"} = $this->profile->id;
        }

        $data->apply($params);
        $data->validate();

        if ($schema->has("--archive")) {
            $this->archive($original_data);
        }

        $crud->update($data);

        return [
            "original"=>$original_data,
            "data"=>$data->toArr()
        ];
        return true;
    }


    public function delete(string $name, array $params)
    {
        if (!$this->profile->hasPermission($name, "delete")) {
            throw new \Exception("No permission to edit this file", $name);
        }

        $this->repo_schema->loadBase($name);
        $this->repo_schema->loadChildren();

        $schema = $this->repo_schema->getSchema("");

        $data = new DataSet();
        $data->bind("--id", $schema->get("--id"));
        $data->{"--id"} = $params["--id"];

        if (!$this->profile->allowedAdminPrivilege($name)) {
            echo "\nWe are building secure?";
            $this->repo_schema->loadToSecure();
            $top = $this->repo_schema->getTop();

            $data->bind("--owner", $top->get("--owner"));
            $data->{"--owner"} = $this->profile->id;
            echo "Id is " . $data->{"--owner"};
        }

        $data->validate();


        $crud = new Mappers\MapQuery($this->dbmanager, $this->repo_schema);
        $original_data = $crud->select($data)->first();

        if ($schema->has("--archive")) {
            $this->archive($original_data);
        }

        $crud->delete($data);

        return $original_data;
    }



    public function resort($name, array $params)
    {
        if (!$this->profile->hasPermission($name, "put")) {
            throw Exception();
        }

        $this->repo_schema->loadBase($name);
        $schema = $this->repo_schema->getSchema("");

        $sortCol = $schema->get("--sort");
        $idCol = $schema->get("--id");

       
        $dataSets = [];
        foreach ($params as $row) {
            $data = new DataSet();
            $data->bind("--sort", $sortCol);
            $data->bind("--id", $idCol);
            
            $data->{"--sort"} = $row["--sort"];
            $data->{"--id"} = $row["--id"];

            $data->validate();
            $dataSets[] = $data;
        }

        $stmt = new Mappers\MapQuery($this->dbmanager, $this->repo_schema);
        $stmt->multipleUpdate($dataSets);

        return true;
    }
}
