<?php
namespace GenerCodeOrm\Binds;

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
    

    

 
    public function validate() {
        foreach($this->value as $val) {
            $error = $this->cell->validate($val);
            if ($error) return $error;
        }

        return 0;
    }

}