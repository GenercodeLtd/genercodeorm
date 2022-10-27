<?php
namespace GenerCodeOrm;

use \Illuminate\Support\Fluent;
use \Illuminate\Container\Container;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;
use \Illuminate\FileSystem\FileSystemManager;
use Psr\Log\LoggerInterface;


class GenerCodeContainer extends Container {

     
    function bindCommonDependencies(Fluent $configs) {

        $this->instance('config', $configs);

        $manager = new DatabaseManager($this, new ConnectionFactory($this));
        $this->instance(\Illuminate\Database\Connection::class, $manager->connection());


        $this->instance(Container::class, $this);


        $this->bind(\GenerCodeOrm\FileHandler::class, function($app) {

            $file = new \Illuminate\Filesystem\FilesystemManager($app);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });
 
    }


    function bindUserDependencies(\GenerCodeOrm\Profile $profile) {
        $this->instance(Profile::class, $profile);
        $this->instance(Factory::class, $profile->factory);
    }

}