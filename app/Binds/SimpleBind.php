<?php
namespace GenerCodeOrm\Binds;

class SimpleBind extends Bind {
   
    function __construct(Cells\MetaCell $cell, $value = null) {
        parent::__construct($cell);
        if ($value) $this->setValue($value);
    }

   
    function setValue($value) {
        $this->value = $this->cell->clean($value);
    }


    public function validate() {
        return $this->cell->validate($this->value);
    }

}