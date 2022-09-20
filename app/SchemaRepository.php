<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class SchemaRepository
{
 
    protected $schemas = [];
    protected $factory;
    protected $is_secure = false;

    function __construct(SchemaFactory $factory) {
        $this->factory = $factory;
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

    public function load($slug, $schema)
    {
        $this->schemas[$slug] = $schema;
        $schema->alias = "t" . count($this->schemas);
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }

    public function isSecure() {
        return $this->is_secure;
    }


    public function get($name) : Cells\MetaCell
    {
        $names = $this->splitNames($name);
    
        $schema = $this->getSchema($names[0]);
        return $schema->get($names[1]);
    }


    public function has($name)
    {
        $names = $this->splitNames($name);
        if (!isset($this->schemas[$names[0]])) return false;
        return $this->schemas[$names[0]]->has($names[1]);
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
            $this->is_secure = true;
        }

    }

    public function loadTo($limit) {
        $parent = $this->get("--parent");

        while ($parent) {
            $this->load($parent->reference, ($this->factory)($parent->reference));
            if ($parent->reference == $limit OR !$this->has($parent->reference . "/--parent")) {
                break;
            }
            
            $parent = $this->get($parent->reference . "/--parent");
        }
    }


    public function loadChildren(?array $children = null, Cells\MetaCell $id = null) {
        if (!$id) $id = $this->get("--id");
        foreach ($id->reference as $child) {
            if (!$children OR in_array($child, $children)) {
                $this->load($child, ($this->factory)($child));
                $this->loadChildren($children, $this->get($child . "/--id"));
            }
        }
    }

    public function loadReferences($fields) {
        foreach($fields as $slug=>$group) {
            $schema = $this->getSchema($slug);
            $slug_alias = (!$slug) ? "" : $slug . "/";
            foreach($group as $field) {
                $cell = $schema->get($field);
                if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
                    $this->load($slug_alias . $field, ($this->factory)($cell->reference));
                }
            }
        }
    }

}
