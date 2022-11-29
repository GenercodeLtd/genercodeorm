<?php
namespace GenerCodeOrm;

use \Illuminate\Container\Container;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;
use \Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;

class GenerCodeServiceProvider extends ServiceProvider {


    function register() {
        
        
        $this->bind(\Illuminate\Database\Connection::class, function($app) {
            return $this->app["db"]->connection();
        });

        $this->instance("db", function($app) {
            return new DatabaseManager($app, new ConnectionFactory($app));
        });

        $this->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });


        $this->bind(\Illuminate\Auth\DatabaseUserProvider::class, function ($app) {
            return new \Illuminate\Auth\DatabaseUserProvider($this->make(\Illuminate\Database\Connection::class, Hasher, "users"));
        });

    }



    function boot() {
        if (!$this->app->config->has("factory")) {
            throw new \PtjException("Factory needs to be set in the configs");
        }
        $factory_name = $this->config->get("factory");
        $factory = new $factory_name();

        $auth = $this->get("auth");
        $user = $auth->user();

        if (!$user) {
            $profile = ($factory)("public");
            $profile->id = 0;
        } else {
            $profile = ($factory)($user->type);
            $profile->id = $user->getAuthIdentifier();
        }

        $this->instance(Profile::class, $profile);
        $this->instance(Factory::class, $profile->factory);
    }

}