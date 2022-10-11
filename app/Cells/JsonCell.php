<?php

namespace GenerCodeOrm\Cells;

class JsonCell extends MetaCell {

    protected $cells = [];

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    

    function addCell($cell) {
        $this->cells[$cell->alias] = $cell;
    }


    public function validate($value)
    {
        if (is_string($value)) $value = json_decode($value, true);
        if ($value === null) return ValidationRules::Characters;

        $errs = [];
        if (count($this->cells) > 0) {
            foreach($this->cells as $cell) {
                $val = (isset($value[$cell->alias])) ? $value[$cell->alias] : null;
                if ($validate = $cell->validate($val)) {
                    $errs[$cell->alias] = $validate;
                }
            }
        }

        if (count($errs) > 0) return $errs;
        else return ValidationRules::OK;
    }


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "json";
        $arr["fields"] = [];
        foreach($this->cells as $alias=>$cell) {
            $arr["fields"][$alias] = $cell->toSchema();
        }
        return $arr;
    }
}