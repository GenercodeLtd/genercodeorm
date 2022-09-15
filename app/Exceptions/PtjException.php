<?php
namespace GenerCodeOrm\Exceptions;

class PtjException extends \Exception {
    
    protected $code = 500;
    protected $title = "";
    protected $description = "";
    protected $message = "";

    function __construct($msg, $code = null) {
        $this->message = $msg;
        if ($code) $this->code = $code;
    }

    function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }

    function __set($key, $val) {
        if (property_exists($this, $key)) $this->$key = $val;
    }


    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }


}