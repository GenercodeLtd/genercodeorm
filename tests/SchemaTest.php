<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\MetaCollection;
use GenerCodeOrm\Cells\IdCell;
use GenerCodeOrm\Cells\ReferenceTypes;

require_once(__DIR__ . "/testsetup.php");
require_once(__DIR__ . "/testproject/Fields.php");
require_once(__DIR__ . "/testproject/Models.php");
require_once(__DIR__ . "/testproject/Projects.php");
require_once(__DIR__ . "/testproject/Sections.php");



use \GenerCodeOrm\Schema;

final class SchemaTest extends TestCase
{
    private $collection;

    public function setUp(): void
    {
      
    }


    public function testSchema() {
        $schema = new \GenerCode\Schemas\Projects();
        var_dump($schema->getSchema());
        exit;

        $collection->activateChildren(["models/", "fields/", "pages/"]);
        
        $container = $collection->getContainer();
        $collections = $container->getAll();
      
        $this->assertSame(4, count($collections));
    }

/*
    public function testParent() {
        $collection = new \GenerCode\Schemas\Fields();
        $collection->activateTo("models/");

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(2, count($collections));
    }

    public function testMultipleParent() {
        $collection = new \GenerCode\Schemas\Fields();
        $collection->activateTo("projects/");

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(3, count($collections));
    }


    public function testActivateReference() {
        $collection = new \GenerCode\Schemas\Fields();
        $collection->activateCells(["section-id"]);

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(2, count($collections));
    }
    */
}