<?php

namespace GenerCodeOrm\Cells;


class AggregatorNumberCell {

    protected $meta_fields = [];
    protected $alias;
    protected $aggregator_type;

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

   

}