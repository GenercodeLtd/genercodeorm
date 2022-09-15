<?php

namespace PressToJamCore\Cells;

class IdCell extends MetaCell {

    protected $reference_type;
    protected $reference = null;
    protected $reference_incoming;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    
   
    function map($value) {
        $value = (is_numeric($value)) ? $value : 0;
        $error = $this->validateSize($value);
        if ($error == ValidationRules::OK) {
            return $value;
        } else {
            return null;
        }
    }


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "id";
        $arr["reference_type"] = $this->reference_type;
        if ($this->is_primary) {
            $arr["children"] = $this->reference;
            $arr["reference_in"] = $this->reference_incoming;
        } else if ($this->is_parent) {
            $arr["reference_to"] = $this->reference->model; 
        } else if ($this->reference) {
            $arr["reference_to"] = $this->reference->model;
            $arr["includes"] = $this->reference->getSummaryAliases();
        }
        return $arr;
    }
}