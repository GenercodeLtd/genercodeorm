<?php
namespace GenerCodeOrm;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\App;
use \Slim\Factory\AppFactory;
use \Slim\Routing\RouteCollectorProxy;
use \Slim\Routing\RouteContext;
use \Slim\Exception\HttpException;
use \Illuminate\Support\Fluent;
use \Illuminate\Container\Container;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;
use \Illuminate\FileSystem\FileSystemManager;
use Psr\Log\LoggerInterface;


class GenerCodeContainer extends Container {

     
    function bindDependencies($configs, $profile_factory) {

        $this->instance('config', $fluent);

        $manager = new DatabaseManager($this, new ConnectionFactory($this));
        $this->instance(\Illuminate\Database\Connection::class, $manager->connection());

        $this->instance("profile", $profile_factory);
        $this->bind(Factory::class, $profile_factory->factory);

        $this->bind(TokenHandler::class, function($app) {
            $token = new TokenHandler();
            $token->setConfigs($app->config->token);
            return $token;
        });


        $this->bind(\GenerCodeOrm\Hooks::class, function($app) {
            $hooks = new \GenerCodeOrm\Hooks($app);
            if ($app->config->hooks) $hooks->loadHooks($app->config->hooks);
            return $hooks;
        });


        $this->bind(\GenerCodeSlim\Queue::class, function($app) {
            return new \GenerCodeSlim\Queue($app);
        });


     /*   $this->bind(\GenerCodeOrm\Model::class, function($app) {
            $dbmanager = $app->get(\Illuminate\Database\DatabaseManager::class);
            $schema = $app->make(\GenerCodeOrm\SchemaRepository::class);
            return new \GenerCodeOrm\Model($dbmanager->connection(), $schema);
        });*/


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

        $this->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });
 
    }

}