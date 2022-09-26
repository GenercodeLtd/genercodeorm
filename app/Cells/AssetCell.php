<?php

namespace GenerCodeOrm\Cells;

class AssetCell extends MetaCell {



    function validateUpload($file) {

    }
 

    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "asset";
        return $arr;
    }

}