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
        $this->error = $error;
        $this->value = $value;
        $this->model = $model;
        $this->message = json_encode([$name=>["error"=>$this->error, "value"=>$this->value, "model"=>$this->model]]);
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

    function getModel() {
        return $this->model;
    }

}