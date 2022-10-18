<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Support\Fluent;

class Model extends Builder
{
    protected EntityManager $en_manager;
    protected $connection;
    protected $name;
    protected ?array $fields = null;
    protected int $secure = 0;
    protected array $where = [];
    protected array $data = [];
    protected array $map = [];
    protected $stmt;

    public function __construct(\Illuminate\Database\Connection $connection, EntityManager $manager, string $name)
    {
        parent::__construct($connection);
        $this->en_manager = $manager;
        $this->connection = $connection;
        $this->name = $name;
        $this->en_manager->loadBase($name);
        $root = $this->en_manager->getEntity("");
        $this->from($root->table, $root->alias);
    }

    public function __set($key, $val)
    {
        if ($key == "name") return;
        if (in_array($key, ["fields", "secure", "where", "data", "map"])) {
            $this->$key = $val;
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


    public function secureQuery(Builder\GenBuilder $query, ?string $to = null) : Binds\SimpleBind {
        $owner = $this->en_manager->loadToSecure($this->secure);
        if ($owner) {
            $bind = new Binds\SimpleBind($owner, $this->secure);
            return $bind;
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
        
    
        $root = $this->en_manager->getSchema("");
        $query = $this->buildQuery($root->table);
        
        $query->insertUsing($this->fields, $oquery);
    }



    public function createDataSet($params)
    {
        $data = new DataSet();
        foreach ($params as $alias=>$val) {
            $cell = $this->en_manager->get($alias)
            if (is_array($val)) {
                if (isset($val['min']) OR isset($val['max'])) {
                    $bind = new Binds\RangeBind($cell, $val);
                } else {
                    $bind = new Binds\SetBind($cell, $val);
                }
            } else {
                $bind = new Binds\SimpleBind($cell, $val);
            }
            $data->addBind($alias, $bind);
        }
        $data->validate();
        return $data;
    }


   


    public function select(DataSet $data)
    {
        $bind = $data->getBind("--id");
        $root = $this->en_manager->getSchema("");
        return $this->buildQuery($root->table, $root->alias)
        ->fields($this->en_manager, $root->cells)
        ->where($root->alias . ".". $bind->cell->name, "=", $bind->value)
        ->take(1)
        ->get()
        ->first();
    }


    public function create($data)
    {
        $data = new DataSet();
        if ($this->en_manager->has("--parent")) {
            $data->bind("--parent", $this->en_manager->get("--parent"));
        }

        $schema = $this->en_manager->getSchema("");
        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and ($cell->required or isset($this->data[$alias]))) {
                $data->bind($alias, $cell);
            } else if ($alias == "--owner" AND $this->secure) {
                $data->bind($alias, $cell);
                $data->{"--owner"} = $this->secure;
            }
        }

        $data->apply($this->data);
        $data->validate();

        $this->checkUniques($data);

        $root = $this->en_manager->getSchema("");
        $query = $this->buildQuery($root->table);

        $id = $query->insertGetId($data->toCellNameArr());

        $arr = $data->toArr();
        $arr["--id"] = $id;

        if ($schema->hasAudit()) {
            $this->audit($id, "POST");
        }

        return $arr;
    }


    public function update()
    {
        $schema = $this->en_manager->getSchema("");

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
        
        foreach ($schema->cells as $alias=>$cell) {
            if (!$cell->system and isset($this->data[$alias]) AND !$cell->immutable) {
                $data->bind($alias, $cell);
            }
        }

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
    }


    public function delete()
    {
        $data = $this->createDataSet($this->where);
        $original_data = $this->select($data);

        if (!$original_data) {
            return [
                "original" => new Fluent([]),
                "affected_rows" => 0
            ];
        }

        $root = $this->en_manager->getSchema("");
        if ($root->hasAudit) {
            $this->audit($data->{"--id"}, "DELETE");
        }

        $this->en_manager->loadChildren();

        
        $query = $this->buildQuery($root->table, $root->alias);
        if ($this->secure) {
            $this->secureQuery($query);
        }
        $query->children($this->en_manager);
        $query->filter($data);
        $count = $query->delete();

        return [
            "original"=>new Fluent($original_data),
            "affected_rows"=>$count
        ];
    }



    public function resort()
    {
        $sortCol = $this->en_manager->get("--sort");
        $idCol = $this->en_manager->get("--id");

        $schema = $this->en_manager->getSchema("");
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
      
        $query->updateStmt([$sortCol->schema->alias . "." . $sortCol->name => 10]);
        foreach($dataSets as $dataSet) {
            $query->execute($dataSet);
        }

        return true;
    }

    public function getAsset($field, $id) {
        $idCell = $this->en_manager->get("--id");
        $data = new DataSet();
        $data->bind("--id", $idCell);
        $data->{"--id"} = $id;
        $res = $this->select($data);
        return $res->{ $field };
    }

    
    
}
