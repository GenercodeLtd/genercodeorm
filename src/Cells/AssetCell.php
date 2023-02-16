<?php

namespace GenerCodeOrm\Cells;

class AssetCell extends MetaCell
{
    protected $file_exts = [];


    function asRules() {
        $rules = parent::asRules();
        if ($this->file_exts) $rules[] = "mimes:" . implode("|", $this->file_exts); 
        return $rules;
    }


    public function toSchema()
    {
        $arr = parent::toSchema();
        $arr["type"] = "asset";
        return $arr;
    }
}
