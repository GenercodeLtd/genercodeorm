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
require(__DIR__ . "/testproject/Profiles.php");
require(__DIR__ . "/testproject/SchemaFactory.php");

final class RepoTest extends TestCase
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


    public function testGet() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["perms"=>["get"], "is_owner"=>false]];

        $factory = new SchemaFactory();

        $repo = new GenerCodeOrm\Repository(
            $this->dbmanager,
            $factory,
            $profile
        );
        
       
        $res = $repo->get("models", ["name"=>"profiles"]);
    
        
        $this->assertSame("profiles", $res->name);
    }


    public function testGetAll() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["perms"=>["get"], "is_owner"=>false]];

        $factory = new SchemaFactory();

        $repo = new GenerCodeOrm\Repository(
            $this->dbmanager,
            $factory,
            $profile
        );
        
       
        $res = $repo->getAll("models");

        $this->assertGreaterThan(1, count($res));
    }


    public function testLimit() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["perms"=>["get"], "is_owner"=>false]];

        $factory = new SchemaFactory();

        $repo = new GenerCodeOrm\Repository(
            $this->dbmanager,
            $factory,
            $profile
        );
        
        $repo->limit  = 3;
        $res = $repo->getAll("models");

        $this->assertSame(3, count($res));
    }


    public function testChildren() {
        $profile = new GenerCodeOrm\Profile();
        $profile->id = 1;
        $profile->type = "test";

        $profile->models = ["models"=>["perms"=>["get"], "is_owner"=>false]];

        $factory = new SchemaFactory();

        $repo = new GenerCodeOrm\Repository(
            $this->dbmanager,
            $factory,
            $profile
        );

        $repo->children = ["fields", "sections"];
        
        $res = $repo->getAll("models");

        $this->assertSame(3, count($res));
    }


   
}