<?php
namespace GenerCodeOrm\Exceptions;

class CellTypeException extends PtjException {

    protected $code = 500;
    protected $message = "";
    protected $title = "Cell type doesn't exist";
    protected $description = "Trying to filter by cell type that doesn't exist";
   

    function __construct($type) {
        $this->message = $type . " doesn't exist";
    }


}