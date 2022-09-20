<?php

namespace GenerCodeOrm\Cells;

class IdCell extends MetaCell {

    protected $reference_type;
    protected $reference = null;
    protected $reference_incoming;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "id";
        $arr["reference_type"] = $this->reference_type;
        $arr["reference"] = $this->reference;
        return $arr;
    }
}