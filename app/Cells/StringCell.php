<?php

namespace GenerCodeOrm\Cells;

class StringCell extends MetaCell {

    protected $encrypted = false;
    protected $tests=[];

    function __construct() {
        parent::__construct();
        $this->default = "";
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else return null;
    }
    

    function setType($data) {
        if (is_array($data)) $this->type = CellValueType::set;
        else $this->type = CellValueType::fixed;
    }


    function setValidation($min, $max, $contains = "", $not_contains = "") {
        $this->min = $min;
        $this->max = $max;
        $this->contains = $contains;
        $this->not_contains = $not_contains;
    }


    function map($value) {
        if (is_array($value)) {
            foreach($value as $key=>$val) {
                $value[$key] = trim($val);
                $this->validate($value[$key]);
                if ($this->last_error != ValidationRules::OK) {
                    return null;
                }
            }
        } else {
            $value = trim($value);
            $this->validate($value);
            if ($this->last_error != ValidationRules::OK) {
                return null;
            }
        }
        return $value;
    }


    function validate($value) {
        $value = trim($value);
        $this->validateSize(strlen($value));
        if ($this->last_error == ValidationRules::OK) {
            $this->validateValue($value);
        }
    }


    function mapToStmtFilter($col) {
        if ($this->type == CellValueType::set) {
            return $col . " LIKE ?";
        } else {
            return $col . " = ?";
        }
    }

    function export($val) {
        if ($this->encrypted) return "xxxxxxxx";
        else return $val;
    }

    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "string";
        if ($this->encrypted) $arr["encrypted"] = true;
        return $arr;
    }

    function getRandom($size, $salt = "", $num_only = false)
	{
		if ($num_only) $permitted_chars = "0123456789";
		else $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code = $salt . substr(str_shuffle($permitted_chars), 0, $size);
		return $code;
	}

    function registerUniqueTest($test) {
        $this->tests[] = $test;
    }


    function toArg($val) {
        if ($this->encrypted) return password_hash($val, PASSWORD_DEFAULT);
        else return $val;
    }

}