<?php

namespace GenerCodeOrm\Cells;

class LogicCell extends MetaCell
{
  
    public function __construct()
    {
        parent::__construct();
    }


    function clean($value) {
        if (is_string($value)) $value = json_decode($value, true);
        return $value;
    }


    public function validate($value)
    {
        if (!$value->field) {
            return ValidationRules::Characters;
        }

        if (!$value->logic_type) {
            return ValidationRules::Characters;
        }

        if (!$value->value) {
            return ValidationRules::Characters;
        }
        return ValidationRules::OK;
    }


    public function toSchema()
    {
        $arr = parent::toSchema();
        $arr["type"] = "logic";
        return $arr;
    }

}
