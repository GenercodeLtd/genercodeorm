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

require(__DIR__ . "/../app/standardfunctions.php");
\GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app");

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
        $collection = new Schema("model");
        $collection->alias = "t1";
       
        $numCell = new NumberCell();
        $numCell->name = "num";
        $numCell->schema = $collection;

        $query = Capsule::table("model", "t1");

        $filters = new MapFilters($query);
        $filters->buildNumber($numCell, ["min"=>5,"max"=>10]); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where `t1`.`num` >= ? and `t1`.`num` <= ?");

    }

    public function testNumberFilterSet() {

        $collection = new Schema("model");
        $collection->alias = "t1";
       

        $numCell = new NumberCell();
        $numCell->name = "num";
        $numCell->schema = $collection;

        $query = Capsule::table("model", "t1");

        $filters = new MapFilters($query);
        $filters->buildNumber($numCell, [10,15,20,25]); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where `t1`.`num` in (?, ?, ?, ?)");

    }


    public function testFlagFilter() {
        
        $collection = new Schema("model");
        $collection->alias = "t1";
    
        $numCell = new FlagCell();
        $numCell->name = "flag";
        $numCell->schema = $collection;

        $query = Capsule::table("model", "t1");

        $filters = new MapFilters($query);
        $filters->buildFlag($numCell, 1); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where `t1`.`flag` = ?");

    }


    public function testStringFilterSet() {

        $collection = new Schema("model");
        $collection->alias = "t1";
       
        $strCell = new StringCell();
        $strCell->name = "str";
        $strCell->schema = $collection;
      
        $query = Capsule::table("model", "t1");

        $filters = new MapFilters($query);
        $filters->buildString($strCell, ["%test%", "%test2%", "%test3%"]); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where (`t1`.`str` like ? or `t1`.`str` like ? or `t1`.`str` like ?)");

    }


    public function testTimeFilterRange() {

        $collection = new Schema("model");
        $collection->alias = "t1";
     
        $timeCell = new TimeCell();
        $timeCell->name = "time";
        $timeCell->schema = $collection;
    
        $query = Capsule::table("model", "t1");

        $filters = new MapFilters($query);
        $filters->buildTime($timeCell,  ["min"=>"2010-08-01 19:56:32"]); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where `t1`.`time` >= ?");

    }

    public function testTimeFilterSet() {

        $collection = new Schema("model");
        $collection->alias = "t1";
        
        $timeCell = new TimeCell();
        $timeCell->name = "time";
        $timeCell->schema = $collection;
    
        $query = Capsule::table("model", "t1");
    

        $filters = new MapFilters($query);
        $filters->buildTime($timeCell, ["2010-08-01 19:56:32", "2010-12-01 19:56:32"]); 

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` as `t1` where `t1`.`time` in (?, ?)");

    }

}