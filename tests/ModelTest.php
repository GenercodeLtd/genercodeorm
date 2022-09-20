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

require(__DIR__ . "/../app/standardfunctions.php");
\GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app");

require(__DIR__ . "/testproject/Fields.php");
require(__DIR__ . "/testproject/Models.php");
require(__DIR__ . "/testproject/Projects.php");
require(__DIR__ . "/testproject/Sections.php");
require(__DIR__ . "/testproject/States.php");
require(__DIR__ . "/testproject/SchemaFactory.php");

final class ModelTest extends TestCase
{


    protected $dbmanager;

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
    }


    public function testPost() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["post"]];

        $factory = new SchemaFactory();
        
        $model = new Model($this->dbmanager, $factory, $profile);
        $res = $model->create("models", ["name"=>"tname", "--parent"=>1]);
        $id = $res["--id"];
        $this->assertNotSame(0, $id);
    }


    public function testDelete() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["delete"]];

        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, $factory, $profile);
        $res = $model->delete("models", ["--id"=>780]);
        //var_dump($res);
        $this->assertSame(15, $model->id);
    }

    public function testUpdate() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["put"]];

        $factory = new SchemaFactory();

        $model = new Model($this->dbmanager, $factory, $profile);
        $res = $model->update("models", ["has-import"=>true, "--id"=>780]);
        var_dump($res);
        $this->assertSame(15, $model->id);
    }


    public function testSlug() {
        $model = new Model();
        $model->{ "collection/name" } = "stuff";
        $this->assertSame("stuff", $model->{ "collection/name" });
    }
}