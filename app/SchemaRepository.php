<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class SchemaRepository
{
 
    protected $schemas = [];
    protected $factory;
 
    function __construct(Factory $factory) {
        $this->factory = $factory;
    }


    public function load($slug, $schema, $with_references = true)
    {
        $this->schemas[$slug] = $schema;
        $schema->alias = "t" . count($this->schemas);
        $this->loadReferences($slug, $schema);
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }

    
    public function splitNames($name) {
        $exp = explode("/", $name);
        if (count($exp) > 2) {
            $cexp = ["", array_pop($exp)];
            $cexp[0] = implode("/", $exp);
            $exp = $cexp;            
        } else if (count($exp) == 1) {
            array_unshift($exp, "");
        }
        return $exp;
    }


    public function get($name, $slug = "") : Cells\MetaCell
    {
        $schema = $this->getSchema($slug);
        return $schema->get($name);
    }


    public function has($name, $slug = "")
    {
        if (!isset($this->schemas[$slug])) return false;
        return $this->schemas[$slug]->has($name);
    }

    public function hasSchema($name)
    {
        return isset($this->schemas[$name]);
    }

    public function getTop() {
        $parent = $this->get("--parent");
        $top = $this->getSchema("");
        while ($top->has("--parent")) {
            $parent = $top->get("--parent");
            $top = $this->getSchema($parent->reference);
        }
        return $top;
    }


    public function getSchema($slug) : Schema {
        if (!isset($this->schemas[$slug])) {
            throw new \Exception("Schema not set in repository: " . $slug);
        }
        return $this->schemas[$slug];
    }

    public function getSchemas() {
        return $this->schemas;
    }

    public function loadBase($base) {
        $this->schemas = []; //clear anything previously loaded in
        $this->load("", ($this->factory)($base));
    }


    public function loadToSecure() {
        $schema = $this->getSchema("");
        $orig_size = count($this->schemas);
        while($schema->has("--parent")) {
            $parent = $schema->get("--parent");
            if (!isset($this->schemas[$parent->reference])) {
                $this->load($parent->reference, ($this->factory)($parent->reference));
            }
            $schema = $this->schemas[$parent->reference];
        }

        if (!$schema->has("--owner")) {
            array_splice($this->schemas, $orig_size, count($this->schemas) - $orig_size);
        } else {
            return $schema->get("--owner");
        }

    }

    public function loadTo($limit) {
        if (!$this->has("--parent")) return;

        $parent = $this->get("--parent");

        while ($parent) {
            $this->load($parent->reference, ($this->factory)($parent->reference));
            if ($parent->reference == $limit OR !$this->has("--parent", $parent->reference)) {
                break;
            }
            
            $parent = $this->get("--parent", $parent->reference);
        }
    }


    public function loadChildren(?array $children = null, Cells\MetaCell $id = null) {
        if (!$id) $id = $this->get("--id");
        foreach ($id->reference as $child) {
            if (!$children OR in_array($child, $children)) {
                $this->load($child, ($this->factory)($child));
                $this->loadChildren($children, $this->get("--id", $child));
            }
        }
    }

    public function loadReferences($slug, $schema) {
        //loop through schemas so that they are loaded in, don't need to connect to all of them necessarily.
        foreach ($schema->cells as $alias=>$cell) {
            if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
                $new_schema = ($this->factory)($cell->reference);
                if ($new_schema->table == $schema->table) continue; //circular reference
                $slug_alias = (!$slug) ? "" : $slug . "/";
                $this->load($slug_alias . $alias, $new_schema);
            }
        }
    }


    public function loadReverseReferences($slug, $schema) {
        //loop through schemas so that they are loaded in, don't need to connect to all of them necessarily.
        $id = $schema->get("--id");
        foreach ($id->reverse_references as $model) {
            if (!isset($this->schemas[$model])) {
                $new_schema = ($this->factory)($model);
                $this->load($model . "/", $new_schema, false); //set to false to prevent a recursive loop
            }
        }
    }

}
