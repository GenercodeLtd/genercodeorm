<?php

namespace GenerCodeOrm\Cells;

abstract class CellValueType
{
    const range = 0;
    const min = 1;
    const max = 2;
    const set = 3;
    const fixed = 4;
}


class MetaCell {

    protected $max = null;
    protected $min = null;
    protected $contains = "";
    protected $not_contains = "";
    protected $name;
    protected $type = CellValueType::fixed;
    protected $default;
    protected $validation_tests = [];
    protected $alias;
    protected $slug;
    protected $immutable = false;
    protected $system = false;
    protected $background = false;
    protected $last_error = null;
    protected $model;
    protected $states = [];
    
    protected $summary = false;


    function __construct() {
        
    }

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else return null;
    }
 

    function setType($data) {
        $this->type = CellValueType::fixed;
    }

    function mapOutput($val) {
        return $val;
    }

    
    function validateSize($size) {
        if ($this->min !== null AND $size < $this->min) {
            $this->last_error = ValidationRules::OutOfRangeMin;
        } else if ($this->max !== null AND $size > $this->max) {
            $this->last_error = ValidationRules::OutOfRangeMax;
        } else {
            $this->last_error = ValidationRules::OK;
        }
    }


    function validateValue($value) {
        if ($this->contains != "" AND !preg_match("/" . $this->contains . "/", $value)) {
            $this->last_error = ValidationRules::Characters;
        } else if ($this->not_contains != "" AND preg_match("/" . $this->not_contains . "/", $value)) {
            $this->last_error = ValidationRules::CharactersNegative;
        } else {
            $this->last_error = ValidationRules::OK;
        }
    }

    function mapToGetStmt() {
        return $this->alias . "." . $this->name;
    }


    function mapToStmtFilter($col) {
        return $col . " = ?";
    }

    function export($val) {
        return $val;
    }

    function toArg($val) {
        return $val;
    }

    function registerValidationTest($test){ 
        $this->validation_tests[] = $test;
    }


    function toSchema() {
        $arr=[];
        $arr["model"] = $this->model;
        $arr["validation"] = [
            "min"=>$this->min, 
            "max"=>$this->max, 
            "contains"=>$this->contains, 
            "notcontains"=>$this->notcontains
        ];
     
        $arr["immutable"] = $this->immutable;
        if ($this->default) $arr["default"] = $this->default;
        if ($this->summary) $arr["summary"] = true;
        if ($this->system) $arr["system"] = true;
        if ($this->background) $arr["background"] = true;
        if ($this->states) {
            $arr["states"] = [];
            foreach($this->states as $state) {
                $arr["states"][] = $state->toSchema();
            }
        }

        return $arr;
    }
 
    function getLastError() {
        return $this->last_error;
    }
}