<?php

namespace GenerCodeOrm\Cells;

class FlagCell extends MetaCell {

   
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
        return $arr;
    }
}