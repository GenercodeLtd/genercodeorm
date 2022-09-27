<?php

namespace GenerCodeOrm\Cells;


class MetaCell
{
    protected $max = null;
    protected $min = null;
    protected $contains = null;
    protected $not_contains = null;
    protected $name;
    protected $default;
    protected $alias;
    protected $immutable = false;
    protected $system = false;
    protected $background = false;
    protected $model;
    protected $states = [];
    protected $summary = false;
    protected $schema;
    

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


    public function setValidation($min, $max, $contains = null, $not_contains = null) {
        $this->min = $min;
        $this->max = $max;
        $this->contains = $contains;
        $this->not_contains = $not_contains;
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


    public function validateValue($value)
    {
        if ($this->contains != "" and !preg_match("/" . $this->contains . "/", $value)) {
            return ValidationRules::Characters;
        } elseif ($this->not_contains != "" and preg_match("/" . $this->not_contains . "/", $value)) {
            return ValidationRules::CharactersNegative;
        } else {
            return ValidationRules::OK;
        }
    }


    public function validate($value, $contains_value = null)
    {
        if ($this->max !== null or $this->min !== null) {
            $error = $this->validateSize($value);
            if ($error != ValidationRules::OK) {
                return $error;
            }
        }

        if ($contains_value !== null) {
            $error = $this->validateValue($contains_value);
            if ($error != ValidationRules::OK) {
                return $error;
            }
        }
    }


    public function clean($value)
    {
        return trim($value);
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
        if ($this->states) {
            $arr["states"] = [];
            foreach ($this->states as $state) {
                $arr["states"][] = $state->toSchema();
            }
        }

        return $arr;
    }
}
