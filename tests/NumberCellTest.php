<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\NumberCell;
use GenerCodeOrm\Cells\ValidationRules;

final class NumberCellTest extends TestCase
{

    private $cell;

    public function setUp() : void {
        $this->cell = new NumberCell();
        $this->cell->min = 10;
        $this->cell->max = 20;
    }

    public function testNoRound() {
        $val = $this->cell->clean(9.2);
        $this->assertSame($val, 9);
    }

    public function testRound() {
        $this->cell->round = 3;
        $val = $this->cell->clean(9.216);
        $this->assertSame($val, 9.21);
    }

}