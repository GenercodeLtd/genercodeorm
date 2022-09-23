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

final class RepoTest extends TestCase {

    protected $dbmanager;

    public function setUp(): void
    {
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


    public function testRepo() {
        $factory = new SchemaFactory();
        $repo = new GenerCodeOrm\Repository($this->dbmanager, $factory);
        $repo->name = "models";
        $repo->where = ["name"=>"tname", "--parent"=>1];
        $repo->order = ["name"=>"desc"];
        $repo->fields = ["name"];
        $repo->limit = 3;
        
        $res = $repo->getAll();
        $id = $res["--id"];
        $this->assertNotSame(0, $id);
    }


   
}