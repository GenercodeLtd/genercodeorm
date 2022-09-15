<?php

namespace GenerCodeOrm\Cells;

class FlagCell extends MetaCell {

    protected $is_primary = false;
    protected $is_parent = false;
    protected $reference = null;
    protected $circular = false;


    function __construct() {
        parent::__construct();
        $this->default = false;
    }


    function setType($data) {
        if (is_array($data)) {
           $this->type = CellValueType::set;
        } else {
            $this->type = CellValueType::fixed;
        }
    }
    

    function setValidation($min, $max) {
        $this->min = $min;
        $this->max = $max;
    }

    function mapOutput($val) {
        return (int) $val;
    }

    function map($value) {
        $value = ($value) ? 1 : 0;
        $this->validateSize($value); 
        if ($this->last_error ==  ValidationRules::OK) {
           return $value;
        } else {
            return null;
        }
    }


    function mapToStmtFilter($col) {
        return $col .= " = ?";
    }


    function export($val) {
        return $val;
    }

    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "flag";
        return $arr;
    }
}