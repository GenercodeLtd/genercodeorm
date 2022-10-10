<?php

namespace GenerCodeOrm\Cells;

class AssetCell extends MetaCell {

    protected $file_exts = [];



    function validateUpload($file) {
        return true;
    }


    public function validate($value)
    {
        if ($this->max !== null or $this->min !== null) {
            $error = $this->validateSize($value['size']);
            if ($error != ValidationRules::OK) {
                return $error;
            }
        }

        $ext = pathinfo($value, \PATHINFO_EXTENSION);
        if (!in_array($ext, $this->file_exts)) {
            return ValidationRules::FileExtension;
        }
    }
 

    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "asset";
        return $arr;
    }

}