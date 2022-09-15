<?php

namespace GenerCodeOrm\Cells;


class DataCell {

    protected $value = null;
    protected $meta_field = null;
    protected $locked = false;
    protected $func = null;
  
    function __construct(MetaCell $field) {
        $this->meta_field = $field;
        $this->value = $this->meta_field->default;
    }
    

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
        else $this->meta_field->$name = $value;
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else return $this->meta_field->$name;
    }

    function __call($name, $args) {
        if (!$args) $args=[$this->value];
        return call_user_func_array([$this->meta_field, $name], $args);
    }
    
    function __toString() {
        return $this->value;
    }

    //values coming from a database. important for number cell
    function mapOutput($val) {
        $this->value = $this->meta_field->mapOutput($val);
    }
    

    function setType() {
        if (is_array($this->value)) {
            if (isset($this->value['min']) AND isset($this->value['max'])) $this->meta_field->type = CellValueType::range;
            else if (isset($this->value['min'])) $this->meta_field->type = CellValueType::min;
            else if (isset($this->value['max'])) $this->meta_field->type = CellValueType::max;
            else $this->meta_field->type = CellValueType::set;
        } else {
            $this->meta_field->type = CellValueType::fixed;
        }
    }


    function export() {
        $fvals;
        if (is_array($this->value)) {
            $fvals = [];
            foreach($this->value as $key=>$val) {
                $fvals[$key] = $this->meta_field->export($val);
            }
        } else {
            $fvals = $this->meta_field->export($this->value);
        }   
        return $fvals;
    }

    function reset() {
        $this->value = null;
    }

    function map($val) {
        if ($this->locked) return;
        $val = $this->meta_field->map($val);
        if ($this->meta_field->last_error != ValidationRules::OK) {
            return $this->meta_field->last_error;
        }
        $this->value = $val;
        $this->setType();
        if ($this->meta_field->immutable) {
            $this->locked = true;
        }
    }


    function mapToStmtFilter($col) {
        if (is_array($this->value)) {
            $cols = [];
            foreach($this->value as $v) {
                $cols[] = $this->meta_field->mapToStmtFilter($col);
            }
            if (count($cols) > 0) {
                return "(" . implode(" OR ", $cols) . ")";
            } else {
                return "";
            }
        } else {
            return $this->meta_field->mapToStmtFilter($col);
        }
    }

    function toArg() {
        if (is_array($this->value)) {
            $vals = [];
            foreach($this->value as $v) {
                $vals[] = $this->meta_field->toArg($v);
            }
            return $vals;
        } else {
            return $this->meta_field->toArg($this->value);
        }
    }
 
}