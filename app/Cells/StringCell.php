<?php

namespace GenerCodeOrm\Cells;

class StringCell extends MetaCell {

    protected $encrypted = false;
    protected $unique = false;
    protected $list = [];
   // protected $

    function __construct() {
        parent::__construct();
        $this->default = "";
    }

    function __get($name) {
        if (property_exists($this, $name)) return $this->$name;
        else return null;
    }
    

    function clean($value) {
        $value = (string) $value ?? '';
        return trim($value);
    }


    function setValidation($min, $max, $contains = "", $not_contains = "") {
        $this->min = $min;
        $this->max = $max;
        $this->contains = $contains;
        $this->not_contains = $not_contains;
    }



    function validate($value, $contains_value = null) {
        return parent::validate(strlen($value), $value);
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


    function toArg($val) {
        if ($this->encrypted) return password_hash($val, PASSWORD_DEFAULT);
        else return $val;
    }

}