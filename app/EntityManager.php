<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class EntityManager
{
 
    protected $entities = [];
    protected $factory;
 
    function __construct(Factory $factory) {
        $this->factory = $factory;
    }


    public function load($slug, $entity, $with_references = true)
    {
        $this->entities[$slug] = $entity;
        $entity->alias = "t" . count($this->entities);
        $this->loadReferences($slug, $entity);
    }

    public function __set($key, $val)
    {
        if (property_exists($this, $key)) {
            $this->$key = $val;
        }
    }

    public function getFactory() {
        return $this->factory;
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
        $entity = $this->getEntity($slug);
        return $entity->get($name);
    }


    public function has($name, $slug = "")
    {
        if (!isset($this->entities[$slug])) return false;
        return $this->entities[$slug]->has($name);
    }

    public function hasEntity($name)
    {
        return isset($this->entities[$name]);
    }

    public function getTop() {
        $parent = $this->get("--parent");
        $top = $this->getEntity("");
        while ($top->has("--parent")) {
            $parent = $top->get("--parent");
            $top = $this->getEntity($parent->reference);
        }
        return $top;
    }


    public function getEntity($slug) : Entity {
        if (!isset($this->entities[$slug])) {
            throw new \Exception("Entity not set in repository: " . $slug);
        }
        return $this->entities[$slug];
    }

    public function getEntities() {
        return $this->entities;
    }

    public function loadBase($base) {
        $this->entities = []; //clear anything previously loaded in
        $this->load("", ($this->factory)($base));
    }


    public function loadToSecure() {
        $entity = $this->getEntity("");
        $orig_size = count($this->entities);
        while($entity->has("--parent")) {
            $parent = $entity->get("--parent");
            if (!isset($this->entities[$parent->reference])) {
                $this->load($parent->reference, ($this->factory)($parent->reference));
            }
            $entity = $this->entities[$parent->reference];
        }

        if (!$entity->has("--owner")) {
            array_splice($this->entities, $orig_size, count($this->entities) - $orig_size);
        } else {
            return $entity->get("--owner");
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

    public function loadReferences($slug, $entity) {
        //loop through Entitys so that they are loaded in, don't need to connect to all of them necessarily.
        foreach ($entity->cells as $alias=>$cell) {
            if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
                $new_schema = ($this->factory)($cell->reference);
                if ($new_schema->table == $entity->table) continue; //circular reference
                $slug_alias = (!$slug) ? "" : $slug . "/";
                $this->load($slug_alias . $alias, $new_schema);
            }
        }
    }


    public function loadReverseReferences($slug, $entity) {
        //loop through schemas so that they are loaded in, don't need to connect to all of them necessarily.
        $id = $entity->get("--id");
        foreach ($id->reverse_references as $model) {
            if (!isset($this->entities[$model])) {
                $new_schema = ($this->factory)($model);
                $this->load($model . "/", $new_schema, false); //set to false to prevent a recursive loop
            }
        }
    }


    public function findChildren(array $children, Entity $entity = null) {
        if (!$entity) $entity = $this->entities[""];
        $id = $entity->get("--id");

        $matches = [];
        foreach($id->reference as $child) {
            if (in_array($child, $children)) {
                $peek = ($this->factory)($child);
                if (!$peek) {
                    $matches[$child] = $peek;
                    continue;
                }

                $res = $this->loadChildren($children, $peek);
                if (!$res) {
                    $matches[$child] = $peek;
                    continue;
                }

                //if we get this far, then we new matches
                $matches = array_merge($matches, $res);
            } else {
                $peek = ($this->factory)($child);
                $res = $this->loadChidlren($children, $peek);
                $matches = array_merge($matches, $res);
            }
        }

        return $matches;
    }

}
