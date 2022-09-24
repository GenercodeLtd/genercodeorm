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


final class ModelTest extends TestCase
{


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
        $this->profile = ($factory)("accounts");
        $this->profile->id = 1;
        
        
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


    
}