<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class Schema
{
    protected $table = "";
    protected $alias;
    protected $cells = [];
    protected $has_export = false;
    protected $has_import = false;
    protected $has_audit = false;
  

 
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


    public function get($name)
    {
        if (isset($this->cells[$name])) return $this->cells[$name];
        else {
            throw new \Exception("Can't get cell of " . $name . " - doesn't exist");
        }
    }


    public function has($kebab)
    {
        return isset($this->cells[$kebab]);
    }

    public function hasAudit() {
        return $this->has_audit;
    }


    public function getSchema() {
        $schema = [];
        if ($this->has_import) $schema["import"] = true;
        if ($this->has_export) $schema["export"] = true;
        if ($this->has_audit) $schema["audit"] = true;
        $schema["schema"] = [];
        foreach ($this->cells as $alias=>$cell) {
            $schema["schema"][$alias] = $cell->toSchema();
        }
        return $schema;
    }
}
