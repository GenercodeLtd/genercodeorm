<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use \Illuminate\Support\Fluent;
use \GenerCodeOrm\Cells\MetaCell;
use \GenerCodeOrm\Cells\ReferenceTypes;
use \Illuminate\Database\Query\Builder;
use \GenerCodeOrm\Exceptions\CellTypeException;

class Model extends Builder
{
    protected $stmt;
    protected Factory $entity_factory;
    protected $entities = [];
    protected $root;
    protected \GenerCodeOrm\Builder\Structure $structure;
    protected \GenerCodeOrm\Builder\Fields $fields_manager;
    protected \GenerCodeOrm\Builder\Filter $filter;
    protected $active = [];
  
    public function __construct(\Illuminate\Database\Connection $connection, Factory $entities, string $name)
    {
        parent::__construct($connection);
        $this->entity_factory = $entities;
        $this->root = $this->load($name);
        $this->entities[$name] = $this->root;
        $this->active[$name] = $this->root;
        $this->structure = new \GenerCodeOrm\Builder\Structure($this);
        $this->fields_manager = new \GenerCodeOrm\Builder\Fields($this);
        $this->filter = new \GenerCodeOrm\Builder\Filter($this);
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
        $entity = ($this->entity_factory)($name);
        $entity->alias = "t" . (count($this->entities) + 1);
        $entity->slug = $slug;

        $key = ($slug) ? $slug : $name;

        $this->entities[$key] = $entity;
        if ($active) $this->addActive($key, $entity);
        return $this->entities[$key];
    }


    public function to($to)
    {
        $this->structure->joinTo($to);
        return $this;
    }


    public function children(?array $children = null)
    {
        $this->structure->loadChildren($children);
        return $this;
    }


    public function secure($profile, $id)
    {
       $this->structure->secureTo($profile, $id);
       return $this;
    }


    public function addReference(Cells\MetaCell $cell)
    {
        $inner = (!$cell->required) ? false : true;
        $ref = $this->load($cell->reference, $cell->getSlug());
        $this->structure->joinIn($cell, $ref->get("--id"), $inner);
    }


    public function fields(?InputSet $fields = null)
    {
        ($this->fields_manager)($fields);
        return $this;
    }

    public function setFromEntity() {
        $alias = (count($this->entities) > 1) ? $this->root->alias : null;
        $this->from($this->root->table, $alias);
        return $this;
    }

    public function filter(\GenerCodeOrm\Binds\Bind $bind) {
        $this->filter->filter($bind);
    }

    public function order(InputSet $aliases) {
        $data = $aliases->getData();
        foreach($aliases as $alias=>$val) {
            if (is_object($val)) {
                $this->order($val);
            } else {
                $cell = $this->getCell($alias, $data->getSlug());
                $this->orderBy($cell->alias, ($val == "DESC") ? "DESC" : "ASC");
            }
        }
    }
    

    public function filterBy(\GenerCodeOrm\DataSet $set) {
        $binds = $set->getBinds();
        foreach($binds as $bind) {
            $this->filter->filter($bind);
        }
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
        $this->insertUsing($this->fields, $model);
    }

   
    
}
