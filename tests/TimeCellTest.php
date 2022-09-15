<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\TimeCell;
use GenerCodeOrm\Cells\ValidationRules;

final class TimeCellTest extends TestCase
{

    private $cell;

    public function setUp() : void {

    }
    /*
    //contains / not contains tests
        public function testContainsValidationFail() : void
        {
            $error = $this->cell->validateSize(2);
        //    $this->assertSame($error, ValidationRules::OutOfRangeMax);
        }

        public function testContainsValidationPass() : void
        {
            $error = $this->cell->validateSize(15);
        //    $this->assertSame($error, ValidationRules::OK);
        }

        public function testNotContainsValidationFail() : void
        {
            $error = $this->cell->validateSize(15);
           // $this->assertSame($error, ValidationRules::OK);
        }

        public function testNotContainsValidationPass() : void
        {
            $error = $this->cell->validateSize(15);
          //  $this->assertSame($error, ValidationRules::OK);
        }

    */
}