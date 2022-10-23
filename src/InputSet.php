<?php

namespace GenerCodeOrm;

class InputValues {
    public $slug;
    public $values;

    function __construct($slug) {
        $this->slug = $slug;
    }

    function addValue($key, $value) {
        $this->values[$key] = $value;
    }
}

class InputSet {

    protected $data = [];
    protected $slug;

    function __construct($slug = "") {
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


    function addData($key, $val) {
        $pts = $this->splitNames($key);
        $slug = (!$pts[0]) ? $this->slug : $pts[0];
        if (!isset($this->data[$slug])) {
            $this->data[$slug] = new InputValues($slug);
        }
        $this->data[$slug]->addValue($pts[1], $val);
    }

    
    function data($inputs) {
        foreach($inputs as $key=>$val) {
            $this->addData($key, $val);
        }
    }

    function getData() {
        return $this->data;
    }


    function getSlug() {
        return $this->slug;
    }

}