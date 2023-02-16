<?php

namespace GenerCodeOrm\Cells;


class MetaCell
{
    protected $max = null;
    protected $min = null;
    protected $name;
    protected $default;
    protected $alias;
    protected $immutable = false;
    protected $system = false;
    protected $background = false;
    protected $model;
    protected $summary = false;
    protected $entity;
    protected $where = [];
    protected $required = false;
    protected $multiple = false;
    
    

    public function __construct()
    {
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function __get($name)
    {
        if ($name == "alias") {
            return $this->alias["kebab"];
        } else if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            return null;
        }
    }


    public function setValidation($min, $max = null) {
        $this->min = $min;
        $this->max = $max;
    }

    public function asRules() {
        $rules = [];
        if ($this->min !== null) $rules[] = "min:" . $this->min;
        if ($this->max !== null) $rules[] = "max:" . $this->max;
        return $rules;
    }


    public function clean($value)
    {
        if (is_array($value)) {
            foreach($value as $key=>$val) {
                $value[$key] = $this->clean($val);
            }
            return $value;
        } else {
            return trim($value);
        }
    }
    

    public function toSchema()
    {
        $arr=[];
        $arr["min"]=$this->min;
        $arr["max"]=$this->max;
        $arr["contains"]=$this->contains;
        $arr["notcontains"]=$this->notcontains;

        $arr["immutable"] = $this->immutable;
        if ($this->default) {
            $arr["default"] = $this->default;
        }
        if ($this->summary) {
            $arr["summary"] = true;
        }
        if ($this->system) {
            $arr["system"] = true;
        }
        if ($this->background) {
            $arr["background"] = true;
        }
        if ($this->where) {
            $arr["where"] = $this->where;
        }

        if ($this->multiple) {
            $arr["multiple"] = $this->multiple;
        }

        return $arr;
    }
}
