<?php

namespace GenerCodeOrm\Cells;

class IdCell extends MetaCell {

    protected $reference_type;
    protected $reference = null;
    protected $reverse_references = [];
    protected $common;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    

    function addReverseRef($model, $field) {
        $this->reverse_references[$model] = $field;
    }


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "id";
        $arr["reference_type"] = $this->reference_type;
        $arr["reference"] = $this->reference;
        $arr["reverse_references"] = $this->reverse_references;
        $arr["common"] = $this->common;
        return $arr;
    }

    public function validate($value)
    {
        if ($this->required AND !$value) {
            return ValidationRules::OutOfRangeMin;
        } else {
            return ValidationRules::OK;
        }
    }
}