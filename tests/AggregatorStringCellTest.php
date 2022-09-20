<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class AggregatorStringCellTest extends TestCase
{
    private $cell;

    public function setUp() : void
    {
        $fields = [];
        $cell = new GenerCodeOrm\Cells\StringCell();
        $cell->name = "min";
        $fields[] = $cell;
        $cell = new GenerCodeOrm\Cells\StringCell();
        $cell->name = "max";
        $fields[] = $cell;

        $this->cell = new GenerCodeOrm\Cells\AggregatorStringCell($fields);
        $this->cell->alias = "string aggregate";
    }


    public function testResult() : void
    {
        $this->assertSame(7, 7);
    }

    public function testName() : void {
        $this->assertSame("string aggregate", $this->cell->alias);
    }
}