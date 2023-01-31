<?php

namespace GenerCodeOrm\Builder;

use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Support\Fluent;
use \GenerCodeOrm\Cells\MetaCell;
use \GenerCodeOrm\Cells\ReferenceTypes;
use \Illuminate\Database\Query\Builder as QBuilder;
use \GenerCodeOrm\Exceptions\CellTypeException;
use \Illuminate\Container\Container;

class Builder extends QBuilder
{
    use Structure, Fields, Filter, HavingFilter;

    protected $stmt;
    protected $entities = [];
    protected $root;
    protected $active = [];
    protected $entity_factory;
  
    public function __construct(\Illuminate\Database\DatabaseManager $dbmanager, string $name)
    {
        parent::__construct($dbmanager->connection());
        $this->entity_factory = $app->get("entity_factory");
        $this->root = $this->load($name);
        $this->entities[$name] = $this->root;
        $this->active[$name] = $this->root;
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


    public function addActive($alias, $entity) {
        $this->active[$alias] = $entity;
    }



    public function getCell($name, $alias) {
        if (!$alias) {
            return $this->root->get($name);
        } else {
            return $this->entities[$alias]->get($name);
        }
    }



    public function load(string $name, string $slug = "", $active = true)
    {
        $entity = ($this->entity_factory)->create($name);
        $entity->alias = "t" . (count($this->entities) + 1);
        $entity->slug = $slug;

        $key = ($slug) ? $slug : $name;

        $this->entities[$key] = $entity;
        if ($active) $this->addActive($key, $entity);
        return $this->entities[$key];
    }


    public function to($to)
    {
        $this->joinTo($to);
        return $this;
    }


    public function children(?array $children = null)
    {
        $this->loadChildren($children);
        return $this;
    }


    public function secure($profile, $id)
    {
       $this->secureTo($profile, $id);
       return $this;
    }


    public function addReference(Cells\MetaCell $cell)
    {
        $inner = (!$cell->required) ? false : true;
        $ref = $this->load($cell->reference, $cell->getSlug());
        $this->joinIn($cell, $ref->get("--id"), $inner);
        return $this;
    }

    public function setFromEntity($no_alias = false) {
        $alias = (!$no_alias) ? $this->root->alias : null;
        $this->from($this->root->table, $alias);
        return $this;
    }


    public function order(InputSet $aliases) {
        $data = $aliases->getData();
        foreach($data as $vals) {
            foreach($vals->values as $alias=>$val) {
                $cell = $this->getCell($alias, $vals->slug);
                $this->orderBy($cell->getDBAlias(), (strtolower($val) == "desc") ? "DESC" : "ASC");
            }
        }
        return $this;
    }
    

    public function group(InputSet $aliases) {
        $data = $aliases->getData();
        foreach($data as $vals) {
            foreach($vals->values as $alias) {
                $cell = $this->getCell($alias, $vals->slug);
                $this->groupBy($cell->getDBAlias());
            }
        }
        return $this;
    }
    

    public function filterBy(\GenerCodeOrm\DataSet $set) {
        $binds = $set->getBinds();
        foreach($binds as $bind) {
            $this->filter($bind);
        }
        return $this;
    }


    public function havingBy(\GenerCodeOrm\DataSet $set) {
        $binds = $set->getBinds();
        foreach($binds as $bind) {
            $this->having($bind);
        }
        return $this;
    }


    protected function buildStmt($sql) {
        $pdo = $this->connection->getPdo();
        
        try {
            $this->stmt = $pdo->prepare($sql);
            //var_dump($this->getBindings());
            //$this->bindValues($this->stmt, $this->getBindings());
        } catch(\PDOException $e) {
            throw new \GenerCodeOrm\Exceptions\SQLException($sql, [],  $e->getMessage());
        }
    }



    public function updateStmt(array $template)
    {
        $sql = $this->grammar->compileUpdate($this, $template);
        $this->buildStmt($sql);
    }



    public function insertStmt(array $template)
    {
        $sql = $this->grammar->compileInsert($this, $template);
        $this->buildStmt($sql);
    }


    public function deleteStmt() {
        $sql = $this->grammar->compileDelete($this);
        $this->buildStmt($sql);
    }


    public function selectStmt() {
        $sql = $this->grammar->compileDelete($this);
        $this->buildStmt($sql);
    }


    public function execute(DataSet $dataSet) {
        try {
            $this->stmt->execute(array_values($dataSet->toCellNameArr()));
        } catch(\Exception $e) {
            throw new \GenerCodeOrm\Exceptions\SQLException("update", $dataSet->toCellNameArr(), $e->getMessage());
        }
    }



    public function copy($fields, $model)
    {
        $this->insertUsing($fields, $model);
    }

   
    
}
