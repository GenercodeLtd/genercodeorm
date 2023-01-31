<?php

namespace GenerCodeOrm\Models;

use \Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;

use \GenerCodeOrm\Model;

class App {
    
    protected $app;
    protected \GenerCodeOrm\Hooks $hooks;
    protected \GenerCodeOrm\Profile $profile;
    protected $name;

    public function __construct(\GenerCodeOrm\Hooks $hooks, \GenerCodeOrm\Profile $profile, $name) 
    {
        $this->name = $name;
        $this->profile = $profile;
        $this->hooks = $hooks;
    }

    function __set($key, $val) {
        if (property_exists($this, $key)) $this->$key = $val;
    }


    protected function trigger($method, $res)
    {
        return $this->hooks->trigger($this->name, $method, $res);
    }

    protected function builder($name = null) {
        $name ??= $this->name;
        return app()->makeWith(\GenerCodeOrm\Builder\Builder::class, ["name"=>$name]);
    }
}