<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Model;
use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Schema;
use GenerCodeOrm\SchemaRepository;
use GenerCodeOrm\Cells\IdCell;
use GenerCodeOrm\Cells\ReferenceTypes;
use GenerCodeOrm\Mappers\MapQuery;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as Container;
use \Illuminate\Support\Fluent;
use \Illuminate\Database\Connectors\ConnectionFactory;
use \Illuminate\Database\DatabaseManager;

//require_once(__DIR__ . "/../src/standardfunctions.php");
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../ptjmanager/repos/ptj");


final class ReferenceControllerTest extends TestCase
{

    protected $container;
    public function setUp(): void
    {
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
        $fluent = new Fluent($configs);

        $fluent['database.fetch'] = \PDO::FETCH_OBJ;
        $fluent['database.default'] = 'default';
        $connections = $fluent['database.connections'];
        $connections["default"] = $configs["db"];
        $fluent['database.connections'] = $connections;



        $container->instance('config', $fluent);
        $this->container = $container;

        $manager = new DatabaseManager($container, new ConnectionFactory($container));
        //$manager->table("tester")->truncate();
      
        $this->container->instance(\Illuminate\Database\Connection::class, $manager->connection());
        $this->container->bind(GenerCodeOrm\ModelController::class, function($app) {
            return new GenerCodeOrm\ModelController(
                $app
            );
        });

        $factory = new PressToJam\ProfileFactory();
        $profile = ($factory)("accounts");

       
        $profile->id = 1;
        $this->container->instance(\GenerCodeOrm\Profile::class, $profile);
       

        $this->container->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });

        $this->container->bind(GenerCodeOrm\ReferenceController::class, function($app) {
            return new GenerCodeOrm\ReferenceController($app);
        });

        $this->container->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });

        
    }



    public function testWithCommon() {
        $refCont = $this->container->get(GenerCodeOrm\ReferenceController::class);
        $rows = $refCont->load("models", "has-profile-owner", 1);
        var_dump($rows);
        $this->assertGreaterThan(0, count($rows));
    }


    public function testRecursive() {
        $refCont = $this->container->make(\GenerCodeOrm\ReferenceController::class);
        $rows = $refCont->load("models", "--recursive", 1);

        var_dump($rows);
        $this->assertGreaterThan(0, count($rows));
    }


    public function testNoCommon() {
        $refCont = $this->container->get(GenerCodeOrm\ReferenceController::class);
    }


    public function testWithParentCommon() {
        $refCont = $this->container->get(GenerCodeOrm\ReferenceController::class);
        $rows = $refCont->load("fields", "reference", 1);
        $this->assertGreaterThan(0, count($rows));
    }

}