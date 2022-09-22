<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class Model
{
    protected $repo_schema;
    protected $connection;
    protected $name;
    protected int $secure = 0;
    protected array $where = [];
    protected array $data = [];

    public function __construct(\Illuminate\Database\DatabaseManager $dbmanager, SchemaRepository $schema)
    {
        $this->repo_schema = $schema;
        $this->connection = $dbmanager->connection();
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
            if ($key == "name") {
                $this->repo_schema->loadBase($val);
            }
        }
    }


    public function buildQuery($table, $alias = null)
    {
        $builder = new Builder\GenBuilder($this->connection);
        $builder->from($table, $alias);
        return $builder;
    }


    public function secureQuery($query, $to = "") {
        $owner = $this->repo_schema->loadToSecure($this->secure);
        if ($owner) {
            $data = new DataSet();
            $data->bind("--owner", $owner);
            $data->{"--owner"} = $this->secure;
            $data->validate();
            $query->secure($this->repo_schema, $data, $to);
        }
    }

    public function multipleUpdate(array $data)
    {
        if (count($data) == 0) {
            return;
        }

        $this->setTable();

        $root = $this->repo_schema->getSchema("");

        $dataSet = $data->first;

        $cols = [];
        foreach ($dataSet->values as $alias =>$bind) {
            if ($alias == "--id") {
                continue;
            }
            $cols[] = "`" . $bind->cell->schema->alias . "`.`" . $bind->cell->name . "` = ?";
        }

        $id = $dataSet->{"--id"};
        $sql = "UPDATE `" . $root->table . "` AS `" . $root->alias . "` SET ";
        $sql .= implode(", ", $cols);
        $sql .= " WHERE `" . $id->cell->schema->alias . "`.`" . $id->cell->name . "` = ?";

        $pdo = $this->connection->getPdo();

        try {
            $stmt = $pdo->prepare($sql);
        } catch(\PDOException $e) {
            throw new Exceptions\SQLException($sql, $args, $e->getMessage());
        }


        foreach ($data as $dataSet) {
            try {
                $stmt->execute($dataSet->toArr());
            } catch(\PDOException $e) {
                throw new Exceptions\SQLException($this->sql, $args, $e->getMessage());
            }
        }
    }


    public function copy($fields, $query)
    {
        $this->setTable(false);
        $root = $this->repo_schema->getSchema("");

        $fields = [];
        foreach ($data->getBinds() as $alias=>$bind) {
            $fields[] = $bind->cell->name;
        }

        $query->insertUsing($fields, $query);
    }



    private function createDataSet($params)
    {
        $data = new DataSet();
        foreach ($params as $alias=>$val) {
            $data->bind($alias, $this->repo_schema->get($alias));
            $data->$alias = $val;
        }
        $data->validate();
        return $data;
    }


    private function archive(Schema $schema, $data)
    {
        $cols = [];

        foreach ($data as $key=>$val) {
            if ($key == "date_created" or $key == "last_updated") {
                continue;
            }
            $cols[$key] = $val;
        }

        $query = $this->buildQuery($schema->table . "_archive");
        $query->insert($cols);
    }


    private function checkUniques(\GenerCodeOrm\DataSet $data)
    {
        $root = $this->repo_schema->getSchema("");
        $id_cell = $root->get("--id");

        $binds = $data->getBinds();
        foreach ($binds as $alias=>$bind) {
            if ($bind->cell->unique) {
                $mdata = new \GenerCodeOrm\DataSet();
                $mdata->bind($alias, $bind->cell);
                $mdata->$alias = $data->$alias;

                if (isset($binds["--id"])) {
                    $mdata->bind("--id", $binds["--id"]->cell);
                    $mdata->{"--id"} = $data->{"--id"};
                }

                if (isset($binds["--parent"])) {
                    $mdata->bind("--parent", $binds["--parent"]->cell);
                    $mdata->{"--parent"} = $data->{"--parent"};
                }

                $query = $this->buildQuery($root->table);
                $query->addSelect($bind->cell->name);
                $query->filter($data);
                $res = $query->take(1)->get();
                if (count($res) > 0) {
                    throw new Exceptions\UniqueException($alias, $data->$alias);
                }
            }
        }
    }


    public function create()
    {
        $data = new DataSet();
        if ($this->repo_schema->has("--parent")) {
            $data->bind("--parent", $this->repo_schema->get("--parent"));
        }

        $schema = $this->repo_schema->getSchema("");
        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and ($cell->required or isset($this->data[$alias]))) {
                $data->bind($alias, $cell);
            }
        }

        $data->apply($this->data);
        $data->validate();

        $this->checkUniques($data);

        $root = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($root->table);

        $id = $query->insertGetId($data->toCellArr());

        $arr = $data->toArr();
        $arr["--id"] = $id;

        return $arr;
    }


    public function select(DataSet $data)
    {
        $bind = $data->getBind("--id");
        $root = $this->repo_schema->getSchema("");
        return $this->connection->table($root->table)
        ->where($bind->cell->name, "=", $bind->value)
        ->take(1)
        ->get()
        ->first();
    }


    public function update()
    {
        $schema = $this->repo_schema->getSchema("");

        $where_data = $this->createDataSet($this->where);
      
        $original_data = $this->select($where_data);

        $data = new DataSet();
        
        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and isset($this->data[$alias]) AND !$cell->immutable) {
                $data->bind($alias, $cell);
            }
        }

        $data->apply($this->data);
        $data->validate();

        $this->checkUniques($data);

        if ($schema->has("--archive")) {
            $this->archive($original_data, $where_data);
        }

        $root = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($root->table);
        if ($this->secure) $this->secureQuery($query);
        $query->filter($where_data);

        $cols = $data->toArr();
        $query->update($cols);

        return [
            "original"=>$original_data,
            "data"=>$cols
        ];
    }


    public function delete()
    {
        $data = $this->createDataSet($this->where);

        $original_data = $this->select($data);

        if ($this->repo_schema->getSchema("")->has("--archive")) {
            $this->archive($original_data, $data);
        }

        $this->repo_schema->loadChildren();

        $root = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($root->table, $root->alias);
        if ($this->secure) {
            $this->secureQuery($query);
        }
        $query->children($this->repo_schema);
        $query->filter($data);
        $query->delete();

        return $original_data;
    }



    public function resort()
    {
       
        $data = new DataSet();
        if (!$this->secure) {
            $owner = $this->repo_schema->loadToSecure($this->secure);
            if ($owner) {
                $data->bind("--owner", $owner);
                $data->{"--owner"} = $this->secure;
            }
        }

        $data->validate();

        $query = $this->buildQuery($this->name);
        $query->loadTo($this->repo_schema);
        $query->filter($data);

        
        $sortCol = $this->repo_schema->get("--sort");
        $idCol = $this->repo_schema->get("--id");


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

        $query->multipleUpdate($dataSets);

        return true;
    }



    
}
