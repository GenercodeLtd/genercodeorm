<?php

namespace GenerCodeOrm\Cells;

class AssetCell extends MetaCell
{
    protected $file_exts = [];



    public function validateUpload($file)
    {
        return true;
    }


    public function validate($value)
    {
        if (is_array($value)) {
            if ($this->max !== null or $this->min !== null) {
                $error = $this->validateSize($value['size']);
                if ($error != ValidationRules::OK) {
                    return $error;
                }
            }

            $ext = pathinfo($value['tmp_name'], \PATHINFO_EXTENSION);
            if (count($this->file_exts) > 0 and !in_array($ext, $this->file_exts)) {
                return ValidationRules::FileExtension;
            }
        } else {
            $ext = pathinfo($value, \PATHINFO_EXTENSION);
            if (count($this->file_exts) > 0 and !in_array($ext, $this->file_exts)) {
                return ValidationRules::FileExtension;
            }
        }
    }


    public function toSchema()
    {
        $arr = parent::toSchema();
        $arr["type"] = "asset";
        return $arr;
    }
}
