<?php

namespace GenerCodeOrm\Models;

use \Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;

use \GenerCodeOrm\Model;

class AppController
{
    protected $app;
    protected \GenerCodeOrm\Hooks $hooks;
    protected \GenerCodeOrm\Profile $profile;

    public function __construct() 
    {
        $app = app();
        $this->profile = $app->get("profile");
        $this->hooks = $app->make(\GenerCodeOrm\Hooks::class);
    }

    function __set($key, $val) {
        if (property_exists($this, $key)) $this->$key = $val;
    }


    protected function trigger($name, $method, $res)
    {
        return $this->hooks->trigger($name, $method, $res);
    }

    protected function model($name) {
        return app()->makeWith(Model::class, ["name"=>$name]);
    }
}