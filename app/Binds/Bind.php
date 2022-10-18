<?php
namespace GenerCodeOrm\Binds;

class Bind
{
    protected Cells\MetaCell $cell;
    protected $value;

    public function __construct($cell)
    {
        $this->cell = $cell;
    }

    function __get($key) {
        if (property_exists($this, $key)) return $this->$key;
    }
}