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

    function map($value) {
        $value = ($value) ? 1 : 0;
        $error = $this->validateSize($value); 
        if ($error ==  ValidationRules::OK) {
           return $value;
        } else {
            return throw $error;
        }
    }


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "flag";
        return $arr;
    }
}