<?php

namespace GenerCodeOrm\Cells;

class RangeCell {

    protected $min;
    protected $max;

    function __construct($min, $max) {
        $this->min = $min;
        $this->max = $max;
    }


    function toSchema() {
        $arr = [];
        $arr["min"] = $this->min->toSchema();
        $arr["round"] = $this->max->toSchema();
        return $arr;
    }
}