<?php

namespace GenerCodeOrm;

class ImportCSV {

    protected $headers = [];
    protected $asset;

    function __construct($headers, $asset) {
        $this->loadFile($asset);
        $this->headers($headers);
        
    }
 

    function loadFile($asset) {
        $this->asset = fopen($asset, 'r');
    }

    function headers($headers) {
        if (!$headers) $headers = [];
        $arr = fgetcsv($this->asset);
        if (!$arr) return;

        $this->headers = [];

        foreach($arr as $col=>$val) {
            $key = array_search($val, $headers);
            $this->headers[] = ($key !== false) ? $key : $val;
        }
    }

    function next() {
        $arr = fgetcsv($this->asset);
        if (!$arr) return false;

        $vals = [];
        var_dump($this->headers);
        foreach($this->headers as $key=>$header) {
            $vals[$header] = $arr[$key];
        }
        return $vals;
    }

}