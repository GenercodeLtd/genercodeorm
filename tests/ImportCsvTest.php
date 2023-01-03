<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;


use Illuminate\Support\Fluent;
use Illuminate\Container\Container;
use \GenerCodeOrm\Http\Controllers\ModelController;
require_once(__DIR__ . "/testproject/EntityFactory.php");
require_once(__DIR__ . "/testproject/PrimaryBusiness.php");

final class ImportCsvTest extends TestCase
{

    protected $app;

    protected $providers = [
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\Auth\AuthServiceProvider::class,
        \Illuminate\Hashing\HashServiceProvider::class,
        \Illuminate\Session\SessionServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        \Illuminate\Events\EventServiceProvider::class
        ];



    public function setUp() : void {
        $csv_loc = "C:/Users/press/Downloads/NACE_REV2_20221029_125135.csv";
        $_FILES['upload-csv'] = [
            "name" =>"upload.csv",
            "tmp_name" => $csv_loc,
            "size" => filesize($csv_loc)
        ];


        $container = new Container();
        $env = new Fluent([
            "s3bucket"=>"presstojam.com", 
            "s3path"=>"assets",
            "dbname"=>"presstojam_com",
            "dbhost"=>"localhost",
            "dbuser"=>"root",
            "dbpass"=>""
        ]);

        

        $configs = require(__DIR__ . "/testproject/configs.php");
        $configs["hooks"] = [];
        $fluent = new Fluent($configs);

        $fluent['database.fetch'] = \PDO::FETCH_OBJ;
        $fluent['database.default'] = 'default';
        $connections = $fluent['database.connections'];
        $connections["default"] = $configs["db"];
        $fluent['database.connections'] = $connections;


        $profile = new \GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->name = "accounts";
        $profile->models = ["primary-business"=>["perms"=>["post", "get", "put", "delete"]]];

        $container->instance("profile", $profile);


        foreach($this->providers as $prov) {
            $p = new $prov($container);
            $p->register();
            $active[$prov] = $p;
        }

        $container->singleton("entity_factory", function() {
            return new \PressToJam\EntityFactory();
        });

        $container->singleton(\Illuminate\Database\DatabaseManager::class, function ($app) {
            return $app->make('db');
        });


        $container->singleton(\Illuminate\Database\Connection::class, function($app) {
            return $app->make('db')->connection();
        });


        $container->bind(\Illuminate\Contracts\Cookie\QueueingFactory::class, function($app) {
            return $app->get("cookie");
        });

        $container->bind(\Illuminate\Contracts\Session\Session::class, function($app) {
            return $app->get("session");
        });


        $container->bind(\Illuminate\Session\SessionManager::class, function($app) {
            return $app->get("session");
        });


        $container->singleton('filesystem.disk', function ($app) {
            return $app['filesystem']->disk("s3");
        });


        
        //$container->instance("entity_factory", new \PressToJam\EntityFactory());

        $container->instance('config', $fluent);

        $container->instance(Container::class, $container);
        $this->app = $container;
    }


    public function testBulkImport() {
        $controller = $this->app->make(ModelController::class);
        $controller->importFromCSV("primary-business", new Fluent(["headers"=>["company-type"=>"Company_type"]]));
    }

}