<?php

namespace GenerCodeOrm\Cells;

class NumberCell extends MetaCell {

    protected $round = 0;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else return null;
    }


    function setType($data) {
        if (is_array($data)) {
            if (isset($data['min']) AND isset($data['max'])) $this->type = CellValueType::range;
            else if (isset($data['min'])) $this->type = CellValueType::min;
            else if (isset($data['max'])) $this->type = CellValueType::max;
            else $this->type = CellValueType::set;
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
        if (is_array($value)) {
            $cvalues = [];
            foreach ($value as $key=>$val) {
                $val = (is_numeric($val)) ? $val : 0;
                $this->validateSize($val);
                if ($this->last_error != ValidationRules::OK) {
                    $values[$key] = $val;
                } else {
                    return null;
                }
            }
        } else {
            $value = (is_numeric($value)) ? $value : 0;
            $this->validateSize($value);
            if ($this->last_error == ValidationRules::OK) {
                return $value;
            } else {
                return null;
            }
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


    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "number";
        $arr["round"] = $this->round;
        return $arr;
    }
}