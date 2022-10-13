<?php

namespace GenerCodeOrm\Cells;

class StringCell extends MetaCell
{
    protected $unique = false;
    protected $list = [];
    protected $pattern;
    protected $not_pattern;

    public function __construct()
    {
        parent::__construct();
        $this->default = "";
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            return null;
        }
    }


    public function clean($value)
    {
        $value = (string) $value ?? '';
        return trim($value);
    }


    public function setValidation($min, $max, $contains = "", $not_contains = "")
    {
        $this->min = $min;
        $this->max = $max;
        $this->contains = $contains;
        $this->not_contains = $not_contains;
    }



    public function validate($value)
    {
        if ($this->list) {
            if (in_array($value, $this->list) or isset($this->list[$value])) {
                return ValidationRules::OK;
            } else {
                return ValidationRules::Characters;
            }
        } else {
            if ($this->pattern AND !preg_match("/" . $this->pattern . "/", $value)) {
                return ValidationRules::Characters;
            }
            
            if ($this->not_pattern AND preg_match("/" . $this->not_pattern . "/", $value)) {
                return ValidationRules::CharactersNegative;
            }
            return $this->validateSize(strlen($value));
        }
    }


    public function toSchema()
    {
        $arr = parent::toSchema();
        $arr["type"] = "string";
        if ($this->list) {
            $arr["list"] = $this->list;
        }
        if ($this->pattern) {
            $arr["pattern"] = $this->pattern;
        }

        if ($this->not_pattern) {
            $arr["not_pattern"] = $this->not_pattern;
        }
        return $arr;
    }

    public function getRandom($size, $salt = "", $num_only = false)
    {
        if ($num_only) {
            $permitted_chars = "0123456789";
        } else {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $code = $salt . substr(str_shuffle($permitted_chars), 0, $size);
        return $code;
    }


    public function toArg($val)
    {
        if ($this->encrypted) {
            return password_hash($val, PASSWORD_DEFAULT);
        } else {
            return $val;
        }
    }
}
