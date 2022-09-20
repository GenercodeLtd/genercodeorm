<?php
namespace GenerCodeOrm;

class Profile {

    protected $models = [];
    protected $id; //user id
    protected $allow_anonymous = false;

    function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }

    function __set($key, $value) {
        if (property_exists($this, $key)) $this->$key = $value;
    }


    function hasPermission($model, $method) {
        if (!isset($this->models[$model])) return false;
        if (!in_array($method, $this->models[$model])) return false;
        return true;
    } 


    function allowedAdminPrivilege($model) {
        return $this->hasPermission($model, "admin");
    }


    function getSitemap($factory) {
        $routes = [];
        foreach($this->models as $name=>$perms) {
            $schema = $factory($name);
            $route = $schema->getSchema();
            $route["perms"] = $perms;
            $routes[$name] = $route;
        }
        return $routes;
    }


}