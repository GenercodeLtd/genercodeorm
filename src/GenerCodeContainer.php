<?php
namespace GenerCodeOrm;

use \Illuminate\Container\Container;

class GenerCodeContainer extends Container {

    function __construct() {
        parent::__construct();
        $this->instance(Container::class, $this);
    }

    function bindConfigs(\Illuminate\Contracts\Config $configs) {
        $this->instance(\Illuminate\Contracts\Config::class, $configs);
    }


    function bindUserDependencies(\GenerCodeOrm\Profile $profile) {
        $this->instance(Profile::class, $profile);
        $this->instance(Factory::class, $profile->factory);
    }

}