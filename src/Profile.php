<?php
namespace GenerCodeOrm;
use PressToJam\Entity as Entity;

class Profile {

    protected $models = [];
    protected $id = 0; //user id
    protected $assumed_roles = [];
    protected $allow_create = true;
    protected $name = "public";
    protected Factory $factory;

    function __construct() {
        $this->factory = new Factory();
    }

    function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }

    function __set($key, $value) {
        if (property_exists($this, $key)) $this->$key = $value;
    }


    function hasPermission($model, $method) {
        if (!isset($this->models[$model])) return false;
        if (!in_array($method, $this->models[$model]["perms"])) return false;
        return true;
    } 

    function allowAssume($name) {
        return (isset($this->assumed_roles[$name]));
    }

    function allowCreate($name) {
        if (!isset($this->assumed_roles[$name]) OR !$this->assumed_roles[$name]["post"]) {
            return false;
        } else {
            return true;
        }
    }


    function allowAnonymousCreate($name) {
        if (!isset($this->assumed_roles[$name]) OR !$this->assumed_roles[$name]["anon"]) {
            return false;
        } else {
            return true;
        }
    }


    function allowedAdminPrivilege($model) {
        if (!isset($this->models[$model])) return false;
        return $this->models[$model]["admin"];
    }

    function getReferenceCells($factory, $schema, &$routes) {
        foreach($schema->cells as $cell) {
            if ($cell->reference_type == Cells\ReferenceTypes::REFERENCE) {
                //load in this entity as well
                if (!isset($routes[$cell->reference])) {
                    $ref_schema = ($factory)->create($cell->reference);
                    $route = $ref_schema->getSchema();
                    $routes[$cell->reference] = $route;
                    $this->getReferenceCells($factory, $ref_schema, $routes);
                }
            }
        }

        if ($schema->has("--parent")) {
            $parent = $schema->get("--parent");
            if (!isset($routes[$parent->reference])) {
                $schema = ($factory)->create($parent->reference);
                $route = $schema->getSchema();
                $routes[$parent->reference] = $route;
            }
        }
    }


    function getSitemap($factory) {
        $routes = [];
        foreach($this->models as $name=>$details) {
            $schema = ($factory)->create($name);
            $route = $schema->getSchema();
            $route["perms"] = $details["perms"];
            $route["admin"] = $details["admin"];
            $routes[$name] = $route;

            $this->getReferenceCells($factory, $schema, $routes);
        }
        return $routes;
    }

    function toArr() {
        return [
            "name"=>$this->name
        ];
    }


}