<?php

namespace PressToJamCore\Cells;


class AggregatorNumberCell {

    protected $value = null;
    protected $meta_fields = [];
    protected $alias;

    function __construct($fields) {
        $this->meta_fields = $fields;
    }
    

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else if (isset($this->meta_fields[$name])) return $this->meta_fields[$name];
    }

    
    function __toString() {
        return $this->value;
    }

    //values coming from a database. important for number cell
    function mapOutput($val) {
        $this->value = (int) $val;
    }
    

    function export() {
        return $this->value;
    }

    function reset() {
        $this->value = null;
    }


}