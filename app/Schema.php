<?php

namespace GenerCodeOrm;

use GenerCodeOrm\Cells as Cells;

class Schema
{
 
    protected $slug = "";
    protected $table = "";
    protected $alias;
    protected $cells = [];
    protected $has_export = false;
    protected $has_import = false;

    protected static $num = 1;


    public function __construct(String $slug, String $table)
    {
        $this->slug = $slug;
        $this->table = $table;
        $this->alias = "t" . self::$num;
        ++self::$num;
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


    public function hasOwner()
    {
        return method_exists($this, "owner");
    }

    public function hasParent()
    {
        return method_exists($this, "parent");
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



    public function activateChildren(?array $children = null)
    {
        if (!$this->active_cells["--id"]) {
            $this->active_cells["--id"] = $this->getFromAlias("--id");
        }

        foreach ($this->active_cells["--id"]->reference as $details) {
            $slug = $details->getSlug();
            if ($children === null OR in_array($slug, $children)) {
                if (!$this->container->has($slug)) {
                    $col = $details->load();
                    $col->activateCell("--parent");
                } else {
                    $col = $this->container->get($slug);
                }
                $col->activateChildren($children);
            }
        }
    }


    public function activateTo($to, $query)
    {
        if ($this->hasParent()) {
            if (isset($this->cells["--parent"])) {
                $this->active_cells["--parent"] = $this->getFromAlias("--parent");
            }

            $ref = $this->active_cells["--parent"]->reference;
            $slug = $ref->getSlug();
            if (!$this->container->has($slug)) {
                $col = $ref->load();
                $col->activateCell("--id");
            } else {
                $col = $this->container->get($slug);
            }
            if ($slug != $to) {
                $col->activateTo($to);
            }
        }


        $this->buildReferenceJoins($collection);
        if ($collection->has("--parent")) {
            $parent = $collection->get("--parent");
            if ($collection->hasActiveCollection($parent->reference)) {
                $parent_collection = $collection->getActiveCollection($parent->reference);
                $primary = $parent_collection->getActiveCell("--id");
                $this->buildJoin($collection, $parent, $parent_collection, $primary);
                $this->buildUp($collection->getActiveCollection($parent->reference));
            }
        } elseif ($collection->hasActiveCell("--owner")) {
            $owner = $collection->getActiveCell("--owner");
            $this->query->join("users", $collection->table . "." . $owner->name, "=", "users.id");
        }
    }

    public function activateReference($cell)
    {
        $slug = $cell->reference->getSlug();
        if (!$this->container->has($slug)) {
            $refcol = $cell->reference->load();
            $refcol->activateCell("--id");
        }
    }

    public function activateCell($alias)
    {
        $cell = $this->getFromAlias($alias);
        $this->active_cells[$cell_name] = $cell;
        if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
            $this->activateReference($cell);
        }
    }


    //applies on the whole container
    public function activateCells($cells)
    {
        foreach ($cells as $slug) {
            $name_parts = $this->convertName($slug);
            $container_slug = $name_parts["container"];
            $alias = $name_parts["alias"];
            
            if ($this->container->has($container_slug)) {
                $col = $this->container->get($container_slug);

                if ($alias == "*summary") {
                    $aliases = $col->getSummaryAliases();
                    foreach ($aliases as $ialias) {
                        $col->activateCell($ialias);
                    }
                } elseif ($alias == "*") {
                    $aliases = $col->getAllAliases();
                    foreach ($aliases as $ialias) {
                        $col->activateCell($ialias);
                    }
                } else {
                    $col->activateCell($alias);
                }
            }
        }
    }


    public function getSchema() {
        $schema = [];
        if ($this->import) $schema["import"] = true;
        if ($this->export) $schema["export"] = true;
        $schema["schema"] = [];
        foreach ($this->cells as $alias=>$cell) {
            $schema["schema"][$alias] = $cell->toSchema();
        }
        return $schema;
    }
}
