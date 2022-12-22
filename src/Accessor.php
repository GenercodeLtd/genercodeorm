<?php
namespace GenerCodeOrm;


class Accessor {

    protected $data = [];
    protected $chains = [];
    protected $chain;


    function __construct($data) {
        $this->data = (array) $data;
        $keys = array_keys($this->data);
        foreach($keys as $key) {
            $exp = explode("/", $key);
            array_pop($exp);

            $chain = "";
            foreach($exp as $ch) {
                if ($chain) $chain .= "/";
                $chain .= $ch;
                $this->chains[$chain] = $chain;
            }
        }
    }


    function __get($key) {
        $key = $this->getRawKeyName($key);
        if (isset($this->data[$this->chain . $key])) {
            $vl = $this->data[$this->chain . $key];
            $this->chain = "";
            return $vl;
        } else {
            if ($this->chain) $this->chain .= "/";
            $this->chain .= $key;
            if (isset($this->chains[$this->chain])) {
                $this->chain .= "/";
                return $this;
            }
        }
        //if no matches, reset chain 
        $this->chain = ""; 
    }


    function __set($key, $val) {
        $key = $this->getRawKeyName($key);
        $this->data[$key] = $val;
    }


    protected function getRawKeyName($key) {
        $key = str_replace(["_"], ["-"], $key);
        return $key;
    }


    function toArr() {
        return $this->data;
    }
}