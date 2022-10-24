<?php
namespace GenerCodeOrm\Exceptions;


class ValidationException extends PtjException {

    protected $code = 500;
    protected $title = "Validation Errors";
    protected $description = "Validation Errors";
    protected $message = "";
   

    function __construct($errors) {
        $this->message = json_encode($errors);
    }

}