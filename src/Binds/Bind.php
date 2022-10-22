<?php
namespace GenerCodeOrm\Binds;

use \GenerCodeOrm\Cells as Cells;
use \GenerCodeOrm\Exceptions as Exceptions;

abstract class Bind
{
    protected \GenerCodeOrm\Cells\MetaCell $cell;
    protected $value;

    public function __construct($cell)
    {
        $this->cell = $cell;
    }

    function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }

    function __set($key, $value) {
        if ($key == "value") $this->setValue($value);
    }

    abstract function setValue($value);

    function finalValidation() {
        $err = $this->validate();
        if ($err != Cells\ValidationRules::OK) {
            throw new Exceptions\ValidationException([$this->cell->name=>$err]);
        }
    }

    abstract function validate();
}