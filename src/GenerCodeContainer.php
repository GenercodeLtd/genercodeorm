<?php
namespace GenerCodeOrm;

use \Illuminate\Container\Container;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;

class GenerCodeContainer extends Container {

    function __construct() {
        $this->instance(Container::class, $this);

        
        $this->singleton(\Illuminate\Database\Connection::class, function($app) {
            $manager = new DatabaseManager($app, new ConnectionFactory($app));
            return $manager->connection();
        });

        $this->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });


        $this->bind(\Illuminate\Auth\DatabaseUserProvider::class, function ($app) {
            return new \Illuminate\Auth\DatabaseUserProvider($this->make(\Illuminate\Database\Connection::class, Hasher, "users"));
        });
    }

    function bindConfigs(\Illuminate\Config\Repository $configs) {
        $this->instance("config", $configs);
    }


    function bindUserDependencies(\GenerCodeOrm\Profile $profile) {
        $this->instance(Profile::class, $profile);
        $this->instance(Factory::class, $profile->factory);
    }

}