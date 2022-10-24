<?php

namespace GenerCodeOrm;

class InputValues
{
    public $slug;
    public $values;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function addValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function addSeqValue($value)
    {
        $this->values[] = $value;
    }
}

class InputSet
{
    protected $data = [];
    protected $slug;

    public function __construct($slug = "")
    {
        $this->slug = $slug;
    }

    public function splitNames($name)
    {
        $exp = explode("/", $name);
        if (count($exp) > 2) {
            $cexp = ["", array_pop($exp)];
            $cexp[0] = implode("/", $exp);
            $exp = $cexp;
        } elseif (count($exp) == 1) {
            array_unshift($exp, "");
        }
        return $exp;
    }


    public function isSequential($arr)
    {
        return (array_keys($arr) !== range(0, count($arr) - 1)) ? false : true;
    }

    public function addData($key, $val)
    {
        $pts = $this->splitNames($key);
        $slug = (!$pts[0]) ? $this->slug : $pts[0];
        if (!isset($this->data[$slug])) {
            $this->data[$slug] = new InputValues($slug);
        }
        $this->data[$slug]->addValue($pts[1], $val);
    }

    public function addSequentialData($val)
    {
        $pts = $this->splitNames($val);
        $slug = (!$pts[0]) ? $this->slug : $pts[0];
        if (!isset($this->data[$slug])) {
            $this->data[$slug] = new InputValues($slug);
        }
        $this->data[$slug]->addSeqValue($pts[1]);
    }


    public function data($inputs)
    {
        if ($this->isSequential($inputs)) {
            foreach ($inputs as $val) {
                $this->addSequentialData($val);
            }
        } else {
            foreach ($inputs as $key=>$val) {
                $this->addData($key, $val);
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }


    public function getSlug()
    {
        return $this->slug;
    }

    public function getValues($slug) {
        return (!isset($this->data[$slug])) ? null : $this->data[$slug];
    }
}
