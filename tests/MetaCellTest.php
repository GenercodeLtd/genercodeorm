<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Cells\ValidationRules;

final class MetaCellTest extends TestCase {

    private $cell;

    public function setUp() : void
    {
        $this->cell = new GenerCodeOrm\Cells\MetaCell();
        $this->cell->model = "model";
        $this->cell->alias = "meta-cell";
        $this->cell->min = 10;
        $this->cell->max = 20;
        $this->cell->contains = "";
        $this->cell->notcontains = "";
        $this->cell->alias = "string aggregate";
    }


    //size validation tests
    public function testMinSizeValidationFail() : void
    {
        $error = $this->cell->validateSize(2);
        $this->assertSame($error, ValidationRules::OutOfRangeMin);
    }

    public function testMaxSizeValidationFail() : void
    {
        $error = $this->cell->validateSize(30);
        $this->assertSame($error, ValidationRules::OutOfRangeMax);
    }

    public function testMinSizeValidationPass() : void
    {
        $error = $this->cell->validateSize(15);
        $this->assertSame($error, ValidationRules::OK);
    }

    public function testMaxSizeValidationPass() : void
    {
        $error = $this->cell->validateSize(15);
        $this->assertSame($error, ValidationRules::OK);
    }


    


    //schema tests

    public function testSchema() : void {
        $schema = $this->cell->toSchema();
        $this->assertSame($schema["model"], "model");
        $this->assertSame($schema["min"], 10);
        $this->assertSame($schema["max"], 20);
    }

}