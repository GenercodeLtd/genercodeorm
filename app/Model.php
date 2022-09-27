<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Support\Fluent;

class Model
{
    protected $repo_schema;
    protected $connection;
    protected $name;
    protected ?array $fields = null;
    protected int $secure = 0;
    protected array $where = [];
    protected array $data = [];
    protected array $map = [];

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

    public function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }


    public function constant($val, $alias = null) {
        return function($builder) use ($val, $alias) {
            $sql = $val;
            if ($alias) $sql .= " AS " . $alias;
            return $builder->raw($sql);
        };
    }


    public function buildQuery($table, $alias = null) : Builder\GenBuilder
    {
        $builder = new Builder\GenBuilder($this->connection);
        $builder->from($table, $alias);
        return $builder;
    }


    public function secureQuery(Builder\GenBuilder $query, ?string $to = null) {
        $owner = $this->repo_schema->loadToSecure($this->secure);
        if ($owner) {
            $data = new DataSet();
            $data->bind("--owner", $owner);
            $data->{"--owner"} = $this->secure;
            $data->validate();
            $query->secure($this->repo_schema, $data, $to);
            return $data;
        }
    }



    public function copy($model)
    {
        $oschema = $model->repo_schema->getSchema("");
        $oquery = $this->buildQuery($oschema->table);

        if (!is_array($model->fields)) {
            throw new \Exception("fields is not an array");
        }

        if (!is_array($this->fields)) {
            throw new \Exception("fields is not an array");
        }

        foreach($model->fields as $olias=>$nalias) {
            if (is_callable($nalias)) {
                $oquery->addSelect($nalias($oquery));
            } else {
                $oquery->addSelect($nalias);
            }
        }

    
        $data = $model->createDataSet($model->where);
        $data->validate();
        $oquery->filter($data, false);
        
    
        $root = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($root->table);
        
        $query->insertUsing($this->fields, $oquery);
    }



    protected function createDataSet($params)
    {
        $data = new DataSet();
        foreach ($params as $alias=>$val) {
            $data->bind($alias, $this->repo_schema->get($alias));
            $data->$alias = $val;
        }
        $data->validate();
        return $data;
    }


    protected function archive(Schema $schema, $data)
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


    protected function checkUniques(\GenerCodeOrm\DataSet $data)
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
                $query->filter($data, false);
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

        $id = $query->insertGetId($data->toCellNameArr());

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

        if (!$original_data) return [
            "original_data"=>null,
            "affected_rows"=>0
        ];

        $data = new DataSet();
        
        foreach ($schema->cells as $alias=>$cell) {
            if ($alias == "--id") {
                $data->bind($alias, $cell);
            } else if (!$cell->system and isset($this->data[$alias]) AND !$cell->immutable) {
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
        $query = $this->buildQuery($root->table, $root->alias);
        if ($this->secure) $this->secureQuery($query);
        $query->filter($where_data);

        $rows = $query->update($data->toCellNameArr());

        return [
            "original"=>new Fluent($original_data),
            "data"=>$data->toArr(),
            "affected_rows"=>$rows
        ];
    }


    public function delete()
    {
        $data = $this->createDataSet($this->where);

        $original_data = $this->select($data);

        if (!$original_data) {
            return null;
        }

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
        $count = $query->delete();

        return [
            "original"=>new Fluent($original_data),
            "affected_rows"=>$count
        ];
    }



    public function resort()
    {
        $sortCol = $this->repo_schema->get("--sort");
        $idCol = $this->repo_schema->get("--id");

        $schema = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($schema->table, $schema->alias);
        $query->filterId($idCol->schema->alias . "." . $idCol->name, 0);

        $odata = $this->secureQuery($query);  
        
        $dataSets = [];
        foreach ($this->data as $row) {
            $data = new DataSet();
            $data->bind("--sort", $sortCol);
            $data->bind("--id", $idCol);
           // $data->bind("--owner")

            $data->{"--sort"} = $row["--sort"];
            $data->{"--id"} = $row["--id"];

            $data->merge($odata);

            $data->validate();
            $dataSets[] = $data;
        }
      
        $query->multipleUpdateStmt([$sortCol->schema->alias . "." . $sortCol->name => 10], $dataSets);

        return true;
    }

    public function getAsset($field, $id) {
        $idCell = $this->repo_schema->get("--id");
        $data = new DataSet();
        $data->bind("--id", $idCell);
        $data->{"--id"} = $id;
        $res = $this->select($data);
        return $res->{ $field };
    }

    
}
