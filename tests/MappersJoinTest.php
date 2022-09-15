<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Schema;
use GenerCodeOrm\Cells\IdCell;
use GenerCodeOrm\Cells\ReferenceTypes;
use GenerCodeOrm\Mappers\MapJoins;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as Container;

final class MappersJoinTest extends TestCase
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

    public function testParentJoin() {

        $collection = new Schema("", "model");

        $idCell = new IdCell();
        $idCell->name = "model_parent_id";
        $idCell->reference_type = ReferenceTypes::PARENT;
        $idCell->reference= "model-parent";
        $collection->addActiveCell("--parent", $idCell);

        $refcollection = new Schema("model-parent", "model_parent");
      
        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $refcollection->addActiveCell("--id", $idCell);

        $collection->addActiveCollection("model-parent", $refcollection);

        $query = Capsule::table("model");
        $join = new MapJoins($query);
        $join->buildUp($collection);

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` inner join `model_parent` on `model`.`model_parent_id` = `model_parent`.`id`");

    }


    public function testChildJoin() {
        $collection = new Schema("", "model_parent");

        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $idCell->reference = ["model-child"];
        $collection->addActiveCell("--id", $idCell);


        $refcollection = new Schema("model-child", "model_child");

        $idCell = new IdCell();
        $idCell->name = "model_parent_id";
        $idCell->reference_type = ReferenceTypes::PARENT;
        $refcollection->addActiveCell("--parent", $idCell);

        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $idCell->reference = [];
        $refcollection->addActiveCell("--id", $idCell);

        $collection->addActiveCollection("model-child", $refcollection);

        $query = Capsule::table("model");
        $join = new MapJoins($query);
        $join->buildDown($collection);

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` left join `model_child` on `model_parent`.`id` = `model_child`.`model_parent_id`");
    }

    public function testNestedChildren() {
        $collection = new Schema("", "model_parent");

        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $idCell->reference = ["model-child"];
        $collection->addActiveCell("--id", $idCell);


        $refcollection = new Schema("model-child", "model_child");

        $idCell = new IdCell();
        $idCell->name = "model_parent_id";
        $idCell->reference_type = ReferenceTypes::PARENT;
        $refcollection->addActiveCell("--parent", $idCell);

        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $idCell->reference = [];
        $refcollection->addActiveCell("--id", $idCell);

        $collection->addActiveCollection("model-child", $refcollection);

        $query = Capsule::table("model");
        $join = new MapJoins($query);
        $join->buildDown($collection);

        $sql = $query->toSQL();
        $this->assertSame($sql, "select * from `model` left join `model_child` on `model_parent`.`id` = `model_child`.`model_parent_id`");
    }

    public function buildReferenceCollection() {
        $collection = new Schema("", "model_parent");

        $idCell = new IdCell();
        $idCell->name = "ref";
        $idCell->reference_type = ReferenceTypes::REFERENCE;
        $idCell->reference = "reference";
        $collection->addActiveCell("ref", $idCell);


        $refcollection = new Schema("reference", "reference");

        $idCell = new IdCell();
        $idCell->name = "id";
        $idCell->reference_type = ReferenceTypes::PRIMARY;
        $idCell->reference = [];
        $refcollection->addActiveCell("--id", $idCell);

        $collection->addActiveCollection("reference", $refcollection);

        return $collection;
    }


    public function testReferenceJoinNotRequired() {
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
}