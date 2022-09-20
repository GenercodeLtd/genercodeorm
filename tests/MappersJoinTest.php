<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

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
require(__DIR__ . "/testproject/SchemaFactory.php");


final class MappersJoinTest extends TestCase
{
    protected $dbmanager;

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
        $this->dbmanager = $capsule->getDatabaseManager();

        
    }

    public function testParentJoin() {

        $factory = new SchemaFactory();

        $schemacol = new SchemaRepository($factory);
       
        $schemacol->loadBase("fields");
        $schemacol->loadTo("models");
    
      
        //$query = Capsule::table("model");
        $join = new MapQuery($this->dbmanager, $schemacol);
        $join->joinTo();
    

        $query = $join->getQuery();
        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `fields` as `t1` inner join `models` as `t2` on `t1`.`models_id` = `t2`.`id`");

    }


    public function testChildJoin() {

        $factory = new SchemaFactory();

        $schemacol = new SchemaRepository($factory);
        $schemacol->loadBase("models");
        $schemacol->loadChildren(["fields"]);

        
        //$query = Capsule::table("model");
        $join = new MapQuery($this->dbmanager, $schemacol);
        $join->children();

        $query = $join->getQuery();
        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `models` as `t1` left join `fields` as `t2` on `t1`.`id` = `t2`.`models_id`");
    }

    public function testNestedChildren() {
        $factory = new SchemaFactory();

        $schemacol = new SchemaRepository($factory);
        $schemacol->loadBase("projects");

        $schemacol->loadChildren(["models","fields", "sections"]);


        $join = new MapQuery($this->dbmanager, $schemacol);
        $join->children();

        $schemas = $schemacol->getSchemas();
        

        $query = $join->getQuery();
        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `projects` as `t1` left join `models` as `t2` on `t1`.`id` = `t2`.`projects_id` left join `fields` as `t3` on `t2`.`id` = `t3`.`models_id` left join `sections` as `t4` on `t2`.`id` = `t4`.`models_id`");
    }

    public function testReferenceCollection() {
   
        $factory = new SchemaFactory();

        $schemacol = new SchemaRepository($factory);
        $schemacol->loadBase("fields");
       

        $fieldschema = $schemacol->getSchema("");
        $fields = [""=>[]];
        foreach($fieldschema->cells as $alias=>$cell) {
            $fields[""][] = $alias;
        }

        $schemacol->loadReferences($fields);

        $join = new MapQuery($this->dbmanager, $schemacol);
        $join->fields($fields);
        $query = $join->getQuery();
        $sql = $query->toSQL();

        $osql = "select `t1`.`id` as `--id`, `t1`.`models_id` as `--parent`, `t1`.`archive_id` as `--archive`, `t1`.`_sort` as `--sort`, `t1`.`_recursive_id` as `--recursive`, `t1`.`name` as `name`, `t1`.`type` as `type`, `t1`.`default_val` as `default-val`, `t1`.`min` as `min`, `t1`.`max` as `max`, `t1`.`contains` as `contains`, `t1`.`notcontains` as `notcontains`, `t1`.`summary` as `summary`, `t1`.`required` as `required`, `t1`.`is_unique` as `is-unique`, `t1`.`is_system` as `is-system`, `t1`.`constant` as `constant`, `t1`.`calculated` as `calculated`, `t1`.`section_id` as `section-id`, `t1`.`depends_on` as `depends-on`, `t1`.`depends_val` as `depends-val`, `t1`.`date_created` as `--created`, `t1`.`last_updated` as `--updated` from `fields` as `t1` left join `sections` as `t2` on `t1`.`section_id` = `t2`.`id` left join `fields` as `t3` on `t1`.`depends_on` = `t3`.`id`";
        $this->assertSame($osql, $sql);
    }

/*
    public function testReferenceJoinNotRequired() {
        $factory = new SchemaFactory();

        $schemacol = new SchemaRepository();
        $collection = $this->buildReferenceCollection();

        $query = Capsule::table("model");
        $join = new MapJoins($query);
        $join->buildReferenceJoins($collection);

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` left join `reference` on `model_parent`.`ref` = `reference`.`id`");
    }


    public function testReferenceJoinRequired() {
        $collection = $this->buildReferenceCollection();
        $cell = $collection->getActiveCell("ref");
        $cell->min = 1;

        $query = Capsule::table("model");
        $join = new MapJoins($query);
        $join->buildReferenceJoins($collection);

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` inner join `reference` on `model_parent`.`ref` = `reference`.`id`");
    }
    */
}