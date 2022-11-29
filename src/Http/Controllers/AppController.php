<?php

namespace GenerCodeOrm\Http\Controllers;

use \Illuminate\Container\Container;
use \Illuminate\Support\Fluent;

use \GenerCodeOrm\Model;

class AppController
{
    protected $app;
    protected \GenerCodeOrm\Hooks $hooks;
    protected \GenerCodeOrm\Profile $profile;

    public function __construct(
        Container $app
    ) {
        $this->app = $app;
        $this->profile = $app->get(\GenerCodeOrm\Profile::class);
       // var_dump($this->profile);
       // exit;
        $this->hooks = $app->make(\GenerCodeOrm\Hooks::class);
    }

    protected function checkPermission($name, $perm)
    {
        if (!$this->profile->hasPermission($name, $perm)) {
            throw new Exceptions\UserAuthException("No " . $perm . " permission for " . $name);
        }
    }

    protected function trigger($name, $method, $res)
    {
        return $this->hooks->trigger($name, $method, $res);
    }

    protected function model($name) {
        return $this->app->makeWith(Model::class, ["name"=>$name]);
    }
}