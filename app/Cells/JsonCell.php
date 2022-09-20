<?php

namespace GenerCodeOrm\Cells;

class JsonCell extends MetaCell {

    protected $cells = [];

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }
    


    function toSchema() {
        $arr=parent::toSchema();
        $arr["type"] = "json";
        $arr["fields"] = [];
        foreach($this->cells as $cell) {
            $arr["fields"][] = $cell->toSchema();
        }
        return $arr;
    }
}