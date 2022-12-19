<?php

namespace GenerCodeOrm\Cells;

class NumberCell extends MetaCell {

    protected $round = 0;
    protected $is_currency = false;
    protected $range = false;

    function __construct() {
        parent::__construct();
        $this->default = 0;
    }



    function setType($data) {
        if (is_array($data)) {
            if (isset($data['min']) AND isset($data['max'])) $this->type = CellValueType::range;
            else if (isset($data['min'])) $this->type = CellValueType::min;
            else if (isset($data['max'])) $this->type = CellValueType::max;
            else $this->type = CellValueType::set;
        } else {
            $this->type = CellValueType::fixed;
        }
    }
    


    function clean($val) {
        return ($this->round > 0) ? (float) $val : (int) $val;
    }



    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "number";
        $arr["round"] = $this->round;
        if ($this->range) {
            $arr["range"] = true;
        }
        return $arr;
    }
}