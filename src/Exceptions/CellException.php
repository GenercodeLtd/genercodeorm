<?php
namespace GenerCodeOrm\Exceptions;

class CellException extends PtjException {

    protected $code = 500;
    protected $message = "";
    protected $title = "Field doesn't exist";
    protected $description = "Trying to access field that doesn't exist";
   

    function __construct($model_name, $name) {
        $this->message = "Field: " . $model_name . "::" . $name . " doesn't exist";
    }


}