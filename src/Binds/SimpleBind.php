<?php
namespace GenerCodeOrm\Binds;

use \GenerCodeOrm\Exceptions\ValidationException;
use \GenerCodeOrm\Cells\ValidationRules;

class SimpleBind extends Bind {
   
    function __construct(\GenerCodeOrm\Cells\MetaCell $cell, $value = null) {
        parent::__construct($cell);
        if ($value) $this->setValue($value);
    }

   
    function setValue($value) {
        $this->value = $this->cell->clean($value);
    }


    public function validate($title = "") {
        $err = $this->cell->validate($this->value);
        if ($err != ValidationRules::OK) {
            throw new ValidationException($this->cell->name, $err, $this->value, $title);
        }
    }

}