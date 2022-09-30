<?php
namespace GenerCodeOrm;


class DataValue {
    public Cells\MetaCell $cell;
    public $value;
}

class DataSet {

    protected $values = [];

    function __construct() {
       
    }

    function __get($key) {
        if (isset($this->values[$key])) {
            return $this->values[$key]->value;
        } else {
            throw new \Exception($key . " is not set");
        }
    }

    function __set($key, $val) {
        if (!isset($this->values[$key])) {
            throw new \Exception("Have to bind " . $key . " first");
        }
        $this->values[$key]->value = $this->values[$key]->cell->clean($val);
    }

    function merge(DataSet $ndata) {
        $this->values = array_merge($this->values, $ndata->getBinds());
    }


    function bind($alias, $cell) {
        $val = new DataValue();
        $val->cell = $cell;
        $this->values[$alias] = $val;
    }

    function bindFromView(DataView $view) {
        foreach($view->getCells() as $alias=>$cell) {
            $this->bind($alias, $cell);
        }
    }

    function getBind($alias) {
        if (!isset($this->values[$alias])) {
            throw new \Exception($alias . " bind doesn't exist");
        }
        return $this->values[$alias];
    }

    
    function apply($params) {
        foreach($params as $key=>$val) {
            $this->$key = $val;
        }
    }


    function getBinds() {
        return $this->values;
    }


    public function toArr() {
        $arr = [];
        foreach($this->values as $key=>$val) {
            $arr[$key] = $val->value;
        } 
        return $arr;
    }


    public function toCellNameArr($alias = "") {
        $arr = [];
        foreach($this->values as $key=>$val) {
            $arr[$alias . $val->cell->name] = $val->value;
        } 
        return $arr;
    }


    public function toCells() {
        $arr = [];
        foreach($this->values as $key=>$val) {
            $arr[$key] = $val->cell;
        } 
        return $arr;
    }


    public function validate() {
        $errors = [];
        foreach($this->values as $slug => $mval) {
            if (is_array($mval->value)) {
                //do something over here with the value
                foreach($mval->value as $ikey=>$ivalue) {
                    $error = $mval->cell->validate($ivalue);
                    if ($error) {
                        $errors[$slug . " " . $ikey] = $error;
                    }
                }
            } else {
                $error = $mval->cell->validate($mval->value);
                if ($error) {
                    $errors[$slug] = $error;
                }
            }
        }

        if (count($errors) > 0) {
            throw new Exceptions\ValidationException($errors);
        }
    }
    

}