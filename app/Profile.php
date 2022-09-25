<?php
namespace GenerCodeOrm;
use PressToJam\Schemas as Schema;

class Profile {

    protected $models = [];
    protected $id = 0; //user id
    protected $allow_anonymous = false;
    protected $allow_create = true;
    protected $name = "public";
    protected Factory $factory;

    function __construct() {
        $this->factory = new Factory();
        $this->factory->addProducts(["users"=> function() {
            return new Schema\Users();
        }]);
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

    function allowCreate() {
        return $this->allow_create;
    }


    function allowAnonymousCreate() {
        return $this->allow_anonymous;
    }


    function allowedAdminPrivilege($model) {
        return $this->hasPermission($model, "admin");
    }


    function getSitemap() {
        $routes = [];
        foreach($this->models as $name=>$perms) {
            $schema = ($this->factory)($name);
            $route = $schema->getSchema();
            $route["perms"] = $perms;
            $routes[$name] = $route;
        }
        return $routes;
    }

    function toArr() {
        return [
            "name"=>$this->name
        ];
    }


}