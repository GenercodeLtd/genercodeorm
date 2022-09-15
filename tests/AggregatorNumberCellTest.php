<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class AggregatorNumberCellTest extends TestCase
{
    private $cell;

    public function setUp() : void
    {
        $fields = [];
        $cell = new GenerCodeOrm\Cells\NumberCell();
        $cell->name = "min";
        $fields[] = $cell;
        $cell = new GenerCodeOrm\Cells\NumberCell();
        $cell->name = "max";
        $fields[] = $cell;

        $this->cell = new GenerCodeOrm\Cells\AggregatorNumberCell($fields);
        $this->cell->alias = "number aggregate";
    }


    public function testResult() : void
    {
        $this->cell->mapOutput(7);
        $this->assertSame(7, $this->cell->export());
    }
}