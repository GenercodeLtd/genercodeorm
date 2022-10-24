<?php
namespace GenerCodeOrm\Binds;

use GenerCodeOrm\Exceptions\ValidationException;
use \GenerCodeOrm\Cells\ValidationRules;

class RangeBind extends Bind {
    

    function __construct(\GenerCodeOrm\Cells\MetaCell $cell, $value = null) {
        parent::__construct($cell);
        $this->value = ["min"=>null, "max"=>null];
        if ($value) {
            $this->setValue($value);
        }
    }


    function setValue($value) {
        if (!is_array($value)) {
            throw new \Exception("Range Bind value must be an array");
        }

        if (isset($value['min'])) {
            $this->value['min'] = $this->cell->clean($value['min']);
        }

        if (isset($value['max'])) {
            $this->value['max'] = $this->cell->clean($value['max']);
        }
    }

 
    public function validate($title = "") {
        $error = $this->cell->validate($this->value['min']);
        if (!$error) {
            $error = $this->cell->validate($this->value['max']);
        }

        if ($error != ValidationRules::OK) {
            throw new ValidationException($this->cell->name, $error, $this->value, $title);
        }
        return $error;
    }

}