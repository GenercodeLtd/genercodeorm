<?php
namespace GenerCodeOrm\Exceptions;

class SQLException extends PtjException {
    
    protected $code = 500;
    protected $title = "SQL Error";
    protected $description = "Sql failed to process";
    protected $message = "";

    function __construct($sql, $args, $msg) {
        $this->message= "SQL: " . $sql . "\n";
        $this->message .= "Args: " . implode(", ", $args) . "\n";
        $this->message .= "Error: " . $msg;
    }


}