<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Schema;
use GenerCodeOrm\Model;
use GenerCodeOrm\SchemaContainer;
use GenerCodeOrm\Cells\IdCell;
use GenerCodeOrm\Cells\FlagCell;
use GenerCodeOrm\Cells\NumberCell;
use GenerCodeOrm\Cells\StringCell;
use GenerCodeOrm\Cells\TimeCell;
use GenerCodeOrm\Cells\ReferenceTypes;
use GenerCodeOrm\Mappers\MapFilters;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as Container;

final class MappersFiltersTest extends TestCase
{
    public function setUp(): void
    {
        $container = new Container();
        $capsule = new Capsule($container);
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'database',
            'username'  => 'root',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
    }

    public function testNumberFilterRange() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $numCell = new NumberCell();
        $numCell->name = "num";
        $collection->addActiveCell("num", $numCell);
        $model->num = ["min"=>5,"max"=>10];

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where `model`.`num` >= ? and `model`.`num` <= ?");

    }

    public function testNumberFilterSet() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $numCell = new NumberCell();
        $numCell->name = "num";
        $collection->addActiveCell("num", $numCell);
        $model->num = [10,15,20,25];

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where `model`.`num` in (?, ?, ?, ?)");

    }


    public function testFlagFilter() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $numCell = new FlagCell();
        $numCell->name = "flag";
        $collection->addActiveCell("flag", $numCell);
        $model->flag = 1;

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where `model`.`flag` = ?");

    }


    public function testStringFilterSet() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $strCell = new StringCell();
        $strCell->name = "str";
        $collection->addActiveCell("str", $strCell);
        $model->str = ["%test%", "%test2%", "%test3%"];

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where (`model`.`str` like ? or `model`.`str` like ? or `model`.`str` like ?)");

    }


    public function testTimeFilterRange() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $timeCell = new TimeCell();
        $timeCell->name = "time";
        $collection->addActiveCell("time", $timeCell);
        $model->time = ["min"=>"2010-08-01 19:56:32"];

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where `model`.`time` >= ?");

    }

    public function testTimeFilterSet() {

        $container = new SchemaContainer();
        $collection = new Schema($container, "", "model");
        $model = new Model();
        

        $timeCell = new TimeCell();
        $timeCell->name = "time";
        $collection->addActiveCell("time", $timeCell);
        $model->time = ["2010-08-01 19:56:32", "2010-12-01 19:56:32"];

        $query = Capsule::table("model");

        $filters = new MapFilters($query);
        $filters->buildFilter($container, $model); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` where `model`.`time` in (?, ?)");

    }

}