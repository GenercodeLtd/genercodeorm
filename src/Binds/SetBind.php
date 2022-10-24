<?php
namespace GenerCodeOrm\Binds;

use GenerCodeOrm\Exceptions\ValidationException;
use \GenerCodeOrm\Cells\ValidationRules;

class SetBind extends Bind {
   
    function __construct(\GenerCodeOrm\Cells\MetaCell $cell, $value = null) {
        parent::__construct($cell);
        $this->value = [];
        if ($value) {
            $this->setValue($value);
        }
    }


    function setValue($value) {
        if (!is_array($value)) {
            throw new \Exception("Set Bind value must be an array");
        }
        
        foreach($value as $key=>$val) {
            $this->value[$key] = $this->cell->clean($val);
        }
    }
    

    

 
    public function validate($title = "") {
        foreach($this->value as $val) {
            $error = $this->cell->validate($val);
            if ($error) {
                if ($error != ValidationRules::OK) {
                    throw new ValidationException($this->cell->name, $error, $val, $title);
                }
            }
        }

        return 0;
    }

}