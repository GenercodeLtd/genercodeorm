<?php
namespace GenerCodeOrm\Exceptions;


class UserAuthException extends PtjException {

    protected $code = 403;
   

    function __construct($message) {
        $this->code = 403;
        $this->title = "User Authorisation Failed";
        $this->description = "User Authorisation Failed";
        $this->message = $message;
    }

}