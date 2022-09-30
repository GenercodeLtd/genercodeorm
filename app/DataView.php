<?php
namespace GenerCodeOrm;

class DataView {

    protected $cells = [];


    function addCell($slug, $cell) {
        $slug = (!$slug) ? "" : $slug . "/";
        $this->cells[$slug . $cell->alias] = $cell;
    }

    function getCells() {
        return $this->cells;
    }
}