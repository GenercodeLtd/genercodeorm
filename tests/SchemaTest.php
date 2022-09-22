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
require_once(__DIR__ . "/testproject/Pages.php");
require_once(__DIR__ . "/testproject/Sections.php");
require_once(__DIR__ . "/testproject/SchemaFactory.php");


use \GenerCodeOrm\Schema;

final class SchemaTest extends TestCase
{
    private $collection;

    public function setUp(): void
    {
      
    }


    public function testSchema() {
        $factory = new SchemaFactory();
        $schema = new \GenerCodeOrm\SchemaRepository($factory);
        $schema->loadBase("projects");
        $schema->loadChildren(["models", "fields", "pages"]);
        
        $containers = $schema->getSchemas();
       
        $this->assertSame(4, count($containers));
    }


    public function testParent() {
        $factory = new SchemaFactory();
        $schema = new \GenerCodeOrm\SchemaRepository($factory);
        $schema->loadBase("fields");
        $schema->loadTo("models");

        $containers = $schema->getSchemas();

        $this->assertSame(2, count($containers));
    }

    public function testSecure() {
        $factory = new SchemaFactory();
        $schema = new \GenerCodeOrm\SchemaRepository($factory);
        $schema->loadBase("fields");
        $schema->loadToSecure();
        $containers = $schema->getSchemas();

        $this->assertSame(3, count($containers));
    }


    public function testActivateReference() {
        $factory = new SchemaFactory();
        $schema = new \GenerCodeOrm\SchemaRepository($factory);
        $schema->loadBase("fields");
        $schema->loadReferences([""=>["section-id"]]);
        $containers = $schema->getSchemas();

        $this->assertSame(2, count($containers));
    }
}