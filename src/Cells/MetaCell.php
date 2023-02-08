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
        if (property_exists($this, $name)) {
            return $this->$name;
        } else {
            return null;
        }
    }


    public function setValidation($min, $max = null) {
        $this->min = $min;
        $this->max = $max;
    }


    public function validateSize($size)
    {
        if ($this->min !== null and $size < $this->min) {
            return ValidationRules::OutOfRangeMin;
        } elseif ($this->max !== null and $size > $this->max) {
            return ValidationRules::OutOfRangeMax;
        } else {
            return ValidationRules::OK;
        }
    }


    public function validate($value)
    {
        if ($this->max !== null or $this->min !== null) {
            $error = $this->validateSize($value);
            if ($error != ValidationRules::OK) {
                return $error;
            }
        }
    }


    public function getDBAlias() {
        return $this->entity->alias . "." . $this->name;
    }


    public function getSlug() {
        $str = "";
        if ($this->entity->slug) $str .= $this->entity->slug . "/";
        return $str . $this->alias;
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
