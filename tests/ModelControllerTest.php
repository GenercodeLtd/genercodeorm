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
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../genercodeltd/repos/ptj");


final class ModelControllerTest extends TestCase
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
        $factory = new ConnectionFactory($container);
        $manager = new DatabaseManager($container, $factory);
        //$manager->table("tester")->truncate();
      
        $this->container->instance(\Illuminate\Database\Connection::class, $manager->connection());
        $this->container->bind(GenerCodeOrm\ModelController::class, function($app) {
            return new GenerCodeOrm\ModelController(
                $app
            );
        });

        $this->container->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });

        $this->container->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });

        $factory = new PressToJam\ProfileFactory();
        $profile = ($factory)("accounts");
     
        $profile->id = 1;
        $this->container->instance(GenerCodeOrm\Profile::class, $profile);


        /*$manager = $capsule->getDatabaseManager();
        $this->container->instance($manager::class, $manager);
        $this->container->instance(GenerCodeOrm\Profile::class, $profile);
        $this->container->bind(GenerCodeOrm\ModelControler::class, function($app) {
            return new GenerCodeOrm\ModelContoller($app->get("db"), $app->make(GenerCodeOrm\Profile::class), $app->make(GenerCodeOrm\Hooks::class));
        });*/
       
       // $this->dbmanager = ;        
    }



    public function testPost() { 
        $_FILES = ["asseter"=> [
            "size"=>500,
            "tmp_name"=>__DIR__ . "/testproject/defaultpdf.pdf",
            "error"=>0,
            "name"=>"defaultpdf.pdf"
        ]];


        $modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $res = $modelCont->create("tester", new Fluent(["stringer"=>"strsy", "number"=>17, "flager"=>1]));
        $this->assertSame(1, $res["--id"]);
    }


    public function testPut() { 
        $_FILES = ["asseter"=> [
            "size"=>500,
            "tmp_name"=>__DIR__ . "/testproject/defaultpdf.pdf",
            "error"=>0,
            "name"=>"defaultpdf.pdf"
        ]];


        $modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $res = $modelCont->update("tester", new Fluent(["--id"=>1, "stringer"=>"strsinger", "number"=>5]));
        $this->assertSame(1, $res["affected_rows"]);
    }


    public function testDelete() {$modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $res = $modelCont->delete("tester", new Fluent(["--id"=>1]));
        $this->assertSame(1, $res["affected_rows"]);
    }


    public function testResort() {
        $this->assertSame(1, 1);
    }

}