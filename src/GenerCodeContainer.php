<?php
namespace GenerCodeOrm;

use \Illuminate\Container\Container;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;

class GenerCodeContainer extends Container {

    function __construct() {
        $this->instance(Container::class, $this);

        $manager = new DatabaseManager($this, new ConnectionFactory($this));
        $this->instance(\Illuminate\Database\Connection::class, $manager->connection());
    }

    function bindConfigs(\Illuminate\Config\Repository $configs) {
        $this->instance("config", $configs);
    }


    function bindUserDependencies(\GenerCodeOrm\Profile $profile) {
        $this->instance(Profile::class, $profile);
        $this->instance(Factory::class, $profile->factory);
    }

}