<?php
namespace GenerCodeOrm\Binds;

class AssetBind extends Bind {
    

    function __construct(\GenerCodeOrm\Cells\MetaCell $cell, $value = null) {
        parent::__construct($cell);
        $this->value = [];
        if ($value) {
            $this->setValue($value);
        }
    }


    function setValue($value) {
        if (!is_array($value)) {
            throw new \Exception("Asset Bind value must be an array");
        }


        //validate then load in file?
        $this->value = $value;
    }

 
    public function validate() {
        $error = $this->cell->validate($this->value);
        return $error;
    }


    public function getBody() {
        return file_get_contents($this->value['tmp_name']);
    }

}