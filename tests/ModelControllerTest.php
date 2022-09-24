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

require_once(__DIR__ . "/../app/standardfunctions.php");
\GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app");
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../ptjmanager/repos/ptj");


final class ModelControllerTest extends TestCase
{

    protected $container;
    public function setUp(): void
    {
        $container = new Container();
        $capsule = new Capsule($container);
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'presstojam_com',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $this->container = $container;
        $manager = $capsule->getDatabaseManager();
      
        $this->container->instance($manager::class, $manager);
        $this->container->bind(GenerCodeOrm\ModelController::class, function($app) {
            return new GenerCodeOrm\ModelController(
                $app->get('Illuminate\Database\DatabaseManager'), 
                $app->make(GenerCodeOrm\Profile::class),
                $app->make(GenerCodeOrm\Hooks::class)
            );
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

  


    public function testCount() { 
        $modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $res = $modelCont->count("projects", []);
        $this->assertSame($res->count, 4);
    }


    public function testReference() { 
        $modelCont = $this->container->get(GenerCodeOrm\ModelController::class);
        $res = $modelCont->count("projects", []);
        $this->assertGreaterThan(1, count($res));
    }


}