<?php
namespace GenerCodeOrm;


class DataValue {
    public Cells\MetaCell $cell;
    public $value;
}

class DataSet {

    protected $binds = [];

    function __construct() {
       
    }

    function __get($key) {
        if (isset($this->binds[$key])) {
            return $this->binds[$key]->value;
        } else {
            throw new \Exception($key . " is not set");
        }
    }

    function __set($key, $val) {
        if (!isset($this->binds[$key])) {
            throw new \Exception("Have to bind " . $key . " first");
        }
        $this->binds[$key]->value = $this->binds[$key]->cell->clean($val);
    }

    function merge(DataSet $ndata) {
        $this->binds = array_merge($this->binds, $ndata->getBinds());
    }


    function addBind($alias, $bind) {
        $this->binds[$alias] = $bind;
    }

    function bindFromView(DataView $view) {
        foreach($view->getCells() as $alias=>$cell) {
            $this->bind($alias, $cell);
        }
    }

    function getBind($alias) {
        if (!isset($this->binds[$alias])) {
            throw new \Exception($alias . " bind doesn't exist");
        }
        return $this->binds[$alias];
    }

    
    function apply($params) {
        foreach($params as $key=>$val) {
            $this->$key = $val;
        }
    }


    function getBinds() {
        return $this->binds;
    }


    public function toArr() {
        $arr = [];
        foreach($this->binds as $key=>$val) {
            $arr[$key] = $val->value;
        } 
        return $arr;
    }


    public function toCellNameArr($alias = "") {
        $arr = [];
        foreach($this->binds as $key=>$val) {
            $arr[$alias . $val->cell->name] = $val->value;
        } 
        return $arr;
    }


    public function toCells() {
        $arr = [];
        foreach($this->binds as $key=>$val) {
            $arr[$key] = $val->cell;
        } 
        return $arr;
    }


    public function validate() {
        $errors = [];
        foreach($this->binds as $slug => $bind) {
            $error = $bind->validate();
            if ($error) {
                $errors[$slug] = $error;
            }
        }

        if (count($errors) > 0) {
            throw new Exceptions\ValidationException($errors);
        }
    }
    

}