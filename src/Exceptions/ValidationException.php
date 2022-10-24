<?php
namespace GenerCodeOrm\Exceptions;


class ValidationException extends PtjException {

    protected $code = 500;
    protected $title = "Validation Errors";
    protected $description = "Validation Errors";
    protected $message = "";
    protected $name;
    protected $error;
    protected $value;

    function __construct($name, $error, $value, $model = "") {
        $arr = [$name => [$error, $value]];
        if ($model) $arr["model"]=$model;
        $this->message = json_encode($arr);
    }

    function getName() {
        return $this->name;
    }

    function getError() {
        return $this->error;
    }

    function getValue() {
        return $this->value;
    }

}