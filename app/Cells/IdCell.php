<?php

namespace GenerCodeOrm\Cells;

class IdCell extends MetaCell {

    protected $is_primary = false;
    protected $is_parent = false;
    protected $is_owner = false;
    protected $reference = null;
    protected $is_recursive = false;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    

    function setValidation($min, $max) {
        $this->min = $min;
        $this->max = $max;
    }

    function mapOutput($val) {
        return (int) $val;
    }

    function map($value) {
        $value = (is_numeric($value)) ? $value : 0;
        $this->validateSize($value);
        if ($this->last_error == ValidationRules::OK) {
            return $value;
        } else {
            return null;
        }
    }


    function mapToStmtFilter($col) {
        if ($this->type == CellValueType::range) {
            return $col . " >= ? AND " . $col . " <= ?";
        } else if ($this->type == CellValueType::min) {
            return $col . " >= ?";
        } else if ($this->type == CellValueType::max) {
            return $col . " <= ?";
        } else {
            return $col .= " = ?";
        }
    }


    function export($val) {
        return $val;
    }

    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "id";
        if ($this->is_primary) {
            $arr["is_primary"] = true;
        } else if ($this->is_parent) {
            $arr["is_parent"] = true;
        } else if ($this->reference) {
            $arr["reference"] = true;
            $arr["reference_to"] = $this->reference->model;
            if (!$this->is_recursive) {
                $arr["includes"] = $this->reference->getSummaryAliases();
            }
        }
        if ($this->is_recursive) {
            $arr["recursive"] = true;
        }
        return $arr;
    }
}