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


        $this->bind(TokenHandler::class, function($app) {
            $token = new TokenHandler();
            $token->setConfigs($app->config->token);
            return $token;
        });

        $this->bind(Container::class, $this);

        $this->bind(\GenerCodeOrm\Hooks::class, function($app) {
            $hooks = new \GenerCodeOrm\Hooks($app);
            if ($app->config->hooks) $hooks->loadHooks($app->config->hooks);
            return $hooks;
        });

/*
        $this->bind(\GenerCodeSlim\Queue::class, function($app) {
            return new \GenerCodeSlim\Queue($app);
        });


        $this->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });


        $this->bind(\GenerCodeOrm\ProfileController::class, function($app) {
            return new \GenerCodeOrm\ProfileController($app);
        });

        $this->bind(\GenerCodeOrm\ModelController::class, function($app) {
            return new \GenerCodeOrm\ModelController($app);
        });

        $this->bind(\GenerCodeOrm\RepositoryController::class, function($app) {
            return new \GenerCodeOrm\RepositoryController($app);
        });

        $this->bind(\GenerCodeOrm\AssetController::class, function($app) {
            return new \GenerCodeOrm\AssetController($app);
        });

        $this->bind(\GenerCodeOrm\ReferenceController::class, function($app) {
            return new \GenerCodeOrm\ReferenceController($app);
        });
        */

        $this->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });
 
    }


    function bindUserDependencies(\GenerCodeOrm\Profile $profile) {
        $this->instance("profile", $profile);
        $this->bind(Factory::class, $profile->factory);
    }

}