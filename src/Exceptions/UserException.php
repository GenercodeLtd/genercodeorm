<?php
namespace GenerCodeOrm\Exceptions;


class UserException extends PtjException {

    protected $code = 401;
    protected $title = "User Authentication Failed";
    protected $description = "User Authentication Failed";
    protected $message = "";

    function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }

}