<?php

namespace GenerCodeOrm\Cells;


class AggregatorStringCell {

    protected $cells = [];
    protected $alias;
    protected $ws;

    function __construct($fields) {
        $this->cells = $fields;
    }
    

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else if (isset($this->cells[$name])) return $this->cells[$name];
    }

    
 
}