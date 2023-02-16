<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class Entity
{
    protected $table = "";
    protected $alias;
    protected $slug;
    protected $cells = [];
    protected $has_export = false;
    protected $has_import = false;
    protected $has_audit = false;
    protected $min_rows = null;
    protected $max_rows = null;
  
 
    public function __construct(String $table)
    {
        $this->table = $table;
    }


    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }

    public function findCell($name, $cells) {
        if (isset($cells[$name])) return $cells[$name];

        foreach($cells as $cell) {
            if (get_class($cell) == Cells\JsonCell::class) {
                $result = $this->findCell($name, $cell->cells);
                if ($result) return $result;
            }
        }
    }


    public function addCell($cell) {
        $this->cells[$cell->alias] = $cell;
    }


    public function get($name, $cells = null)
    {
        $cell = $this->findCell($name, $this->cells);
        if ($cell) return $cell;
        else throw new \Exception("Can't get cell of " . $name . " - doesn't exist");
    }


    public function has($name)
    {
        $cell = $this->findCell($name, $this->cells);
        return ($cell) ? true : false;
    }

    
    public function hasAudit() {
        return $this->has_audit;
    }


    public function getSchema() {
        $schema = [];
        if ($this->has_import) $schema["import"] = true;
        if ($this->has_export) $schema["export"] = true;
        if ($this->has_audit) $schema["audit"] = true;
        if ($this->min_rows) $schema["min_rows"] = $this->min_rows;
        if ($this->max_rows) $schema["max_rows"] = $this->max_rows;
        $schema["schema"] = [];
        foreach ($this->cells as $alias=>$cell) {
            $schema["schema"][$alias] = $cell->toSchema();
        }
        return $schema;
    }
}
