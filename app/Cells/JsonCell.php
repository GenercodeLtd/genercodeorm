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