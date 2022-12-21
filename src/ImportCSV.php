<?php

namespace GenerCodeOrm;

class ImportCSV {

    protected $headers = [];
    protected $asset;
 

    function loadFile($asset) {
        $this->asset = $asset;
    }

    function next() {
        $arr = fgetcsv($this->asset);
        if (!$arr) return false;

        $vals = [];
        foreach($this->headers as $key=>$header) {
            $vals[$header] = $arr[$key];
        }
        return $vals;
    }

}