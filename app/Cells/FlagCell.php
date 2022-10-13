<?php

namespace GenerCodeOrm\Cells;

class FlagCell extends MetaCell {

    protected $on_value;
    protected $off_value;
   
    function __construct() {
        parent::__construct();
        $this->default = false;
    }

   

    function clean($val) {
        return (int) $val;
    }

    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "flag";
        $arr["on_value"] = $this->on_value;
        $arr["off_value"] = $this->off_value;
        return $arr;
    }
}