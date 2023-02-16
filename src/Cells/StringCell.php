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

   

    public function clean($value)
    {
        $value = (string) $value ?? '';
        return trim($value);
    }


  
    public function asRules() {
        $rules = parent::asRules();
        if ($this->pattern !== null) $rules[] = "regex:" . $this->pattern;
        if ($this->not_pattern !== null) $rules[] = "not_regex:" . $this->pattern;
        if ($this->unique) $rules[] = "unique:" . $this->entity->table;
        return $rules;
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

        if ($this->multiple) {
            $arr["multiple"] = true;
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
