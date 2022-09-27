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
//\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../ptjmanager/repos/ptj");
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../api_capstone/repos/v4/ptj");


final class RepoTest extends TestCase {

    protected $dbmanager;
    protected $profile;

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
        $this->dbmanager = $capsule->getDatabaseManager();   
        
        $factory = new PressToJam\ProfileFactory();
        $this->profile = ($factory)("pi-users");
        $this->profile->id = 1;
    }


    public function testRepo() {
        $factory = new Factory();
        $repo = new GenerCodeOrm\Repository($this->dbmanager, new SchemaRepository($this->profile->factory));
        $repo->name = "models";
        $repo->where = ["name"=>"tname", "--parent"=>1];
        $repo->order = ["name"=>"desc"];
        $repo->fields = ["name"];
        $repo->limit = 3;
        
        $res = $repo->getAll();
        $id = $res["--id"];
        $this->assertNotSame(0, $id);
    }


    public function testReference() {
        $repo = new GenerCodeOrm\Repository($this->dbmanager, new SchemaRepository($this->profile->factory));
        $repo->name = "models";
        $repo->where = ["--parent"=>1];
        $res = $repo->getAsReference();
        $this->assertGreaterThan(1, count($res));
    }

    public function testLoadReference() {
        $model = new GenerCodeOrm\Repository($this->dbmanager, new SchemaRepository($this->profile->factory));
        $model->name = "eft-batch-payments";
        $repo = $model->repo_schema;

        $schemas = $repo->getSchemas();
        echo "Total is " . count($schemas);
      //  $repo->loadReferences();
    }


   
}