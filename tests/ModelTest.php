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

require_once(__DIR__ . "/../app/standardfunctions.php");
\GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app");
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../genercodeltd/repos/ptj");


final class ModelTest extends TestCase
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
      
        $this->container->instance($manager::class, $manager);
        $this->container->bind(GenerCodeOrm\Model::class, function($app) {
            return new GenerCodeOrm\Model(
                $app->get(DatabaseManager::class), new SchemaRepository($app->get(GenerCodeOrm\Profile::class)->factory)
            );
        });

        $this->container->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });

        $this->container->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $prefix = $app->config["filesystems.disks.s3"]['prefix_path'];
            $fileHandler = new \GenerCodeOrm\FileHandler($file, $prefix);
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
            return new GenerCodeOrm\ModelController($app->get("db"), $app->make(GenerCodeOrm\Profile::class), $app->make(GenerCodeOrm\Hooks::class));
        });*/
       
       // $this->dbmanager = ;        
    }


    public function testPost() {
        /*$profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["post"]];*/

        $factory = new SchemaFactory();
        
        $model = new Model($this->dbmanager, new SchemaRepository($factory));
        $model->name = "models";
        $model->data = ["name"=>"tname", "--parent"=>1];
        $res = $model->create();
        $id = $res["--id"];
        $this->assertNotSame(0, $id);
    }


    public function testDelete() {
        /*$profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["delete"]];*/

        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, new SchemaRepository($factory));
        $model->name = "models";
        $model->where = ["--id" => 780];
        $res = $model->delete(true);
        //var_dump($res);
        $this->assertSame(15, $res["affected_rows"]);
    }

    public function testUpdate() {
        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, new SchemaRepository($factory));
        $model->name = "models";
        $model->data = ["has-import"=>true];
        $model->where = ["--id"=>780];
        $res = $model->update();
        var_dump($res);
        $this->assertSame(15, $model->id);
    }


    public function testUpdateSecure() {
        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, new SchemaRepository($factory));
        $model->name = "models";
        $model->data = ["has-export"=>true];
        $model->where = ["--id"=>780];
        $model->secure = 1;
        $res = $model->update();
        var_dump($res);
        $this->assertSame(15, $model->id);
    }



    public function testResort() {
        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, new SchemaRepository($factory));
        $model->name = "models";
        $model->data = [["--id"=>1,"--sort"=>1],["--id"=>2, "--sort"=>2],["--id"=>3, "--sort"=>3]];
        $model->secure = 1;
        $res = $model->resort();
        var_dump($res);
        $this->assertSame(15, $model->id);
    }


    public function testCopy() {
        

        $model = $this->container->get(Model::class);
        $model->name = "tester";
        $model->fields = [$model->constant(11), "stringer"];
        $model->where = ["number"=>5];
      
        $emodel = $this->container->get(Model::class);
        $emodel->name = "tester";
        $emodel->fields = ["number", "stringer"];
        $emodel->copy($model);
    }

    
}