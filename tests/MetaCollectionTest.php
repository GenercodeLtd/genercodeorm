<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\MetaCollection;
use GenerCodeOrm\Cells\IdCell;
use GenerCodeOrm\Cells\ReferenceTypes;

require_once(__DIR__ . "/metacollections/Fields.php");
require_once(__DIR__ . "/metacollections/Models.php");
require_once(__DIR__ . "/metacollections/Projects.php");
require_once(__DIR__ . "/metacollections/Pages.php");
require_once(__DIR__ . "/metacollections/Sections.php");


final class MetaCollectionTest extends TestCase
{
    private $collection;

    public function setUp(): void
    {
      
    }


    public function testChildren() {
        $container = new \GenerCodeOrm\MetaCollectionContainer();
        $collection = new \PressToJam\MetaCollections\Projects($container);
        $collection->activateChildren(["models/", "fields/", "pages/"]);
        
        $container = $collection->getContainer();
        $collections = $container->getAll();
      
        $this->assertSame(4, count($collections));
    }


    public function testParent() {
        $container = new \GenerCodeOrm\MetaCollectionContainer();
        $collection = new \PressToJam\MetaCollections\Fields($container);
        $collection->activateTo("models/");

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(2, count($collections));
    }

    public function testMultipleParent() {
        $container = new \GenerCodeOrm\MetaCollectionContainer();
        $collection = new \PressToJam\MetaCollections\Fields($container);
        $collection->activateTo("projects/");

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(3, count($collections));
    }


    public function testActivateReference() {
        $container = new \GenerCodeOrm\MetaCollectionContainer();
        $collection = new \PressToJam\MetaCollections\Fields($container);
        $collection->activateCells(["section-id"]);

        $container = $collection->getContainer();
        $collections = $container->getAll();

        $this->assertSame(2, count($collections));
    }
}