<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Model;


final class ModelTest extends TestCase
{


    public function setUp(): void
    {
     
    }


    public function testValue() {
        $model = new Model();
        $model->name = "test";
        $this->assertSame("test", $model->name);
    }


    public function testID() {
        $model = new Model();
        $model->id = 15;
        $this->assertSame(15, $model->id);
    }

    public function testDataSplit() {
        $model = new Model();
        $model->id = 15;
        $model->name = "stuff";
        $values = $model->getValues();
        $this->assertSame(1, count($values));
    }


    public function testSlug() {
        $model = new Model();
        $model->{ "collection/name" } = "stuff";
        $this->assertSame("stuff", $model->{ "collection/name" });
    }
}