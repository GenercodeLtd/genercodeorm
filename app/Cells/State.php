<?php

namespace PressToJamCore\Cells;

class State
{
    protected $depends_on;
    protected $depends_val;
    protected $default = false;
    protected $field;


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
        }
    }


    function toSchema() {
        $arr=[];
        if (!$this->depends_val) $this->default = true;
        $arr["depends_on"] = $this->depends_on;
        $arr["depends_val"] = $this->depends_val;
        $arr["data"] = $this->field->toSchema();
        $arr["default"] = $this->default;
        return $arr;
    }
}