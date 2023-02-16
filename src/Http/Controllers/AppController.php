<?php

namespace GenerCodeOrm\Http\Controllers;

use \Illuminate\Container\Container;
use \Illuminate\Support\Fluent;
use \GenerCodeOrm\Exceptions as Exceptions;

use \GenerCodeOrm\Builder\Builder;

class AppController
{
    protected $app;
    protected \GenerCodeOrm\Hooks $hooks;
    protected \GenerCodeOrm\Profile $profile;
    protected $alias;

    public function __construct(
    ) {
        $this->app = app();
        $this->profile = $this->app->get("profile");
        $this->profile = new \PressToJam\Profile\AccountsProfile();
        $this->profile->id = 1;
       // var_dump($this->profile);
       // exit;
        $this->hooks = $this->app->make(\GenerCodeOrm\Hooks::class);
    }

    protected function checkPermission($perm)
    {
        if (!$this->profile->hasPermission($this->alias, $perm)) {
            throw new Exceptions\UserAuthException("No " . $perm . " permission for " . $this->alias);
        }
    }

    protected function trigger($method, $res)
    {
        return $this->hooks->trigger($this->alias, $method, $res);
    }

}