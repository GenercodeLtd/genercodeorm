<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells as Cells;
use GenerCodeOrm\MetaCollection;
use GenerCodeOrm\Validator;
use GenerCodeOrm\Model;


final class ValidatorTest extends TestCase
{

    private $validator;

    public function setUp(): void
    {
        $cell = new Cells\NumberCell();
        $cell->name = "num";
        $cell->min = 10;
        $cell->max = 20;

        $this->validator = new Validator();
        $this->validator->num = $cell;
    }


    public function testValidatorFail() {
        $model = new Model();
        $model->num = 2;
        $errors = [];
        $pass = false;
        try {
            $this->validator->validate($model);
            $pass = true;
        } catch(\Exception $e) {
            $errors = $e;
        }

        $this->assertSame($pass, false);
    }


    public function testFilterFail() {
        $model = new Model();
        $model->num = ["min"=>15, "max"=>25];
        $errors = [];
        $pass = false;
        try {
            $this->validator->validate($model);
            $pass = true;
        } catch(\Exception $e) {
            $errors = $e;
        }

        $this->assertSame($pass, false);
    }



    public function testFilterIDFail() {
        $validator = new Validator();
        $cell = new Cells\IdCell();
        $cell->name = "id";
        $cell->min = 1;
        $cell->max = 10000000;
        $validator->id = $cell;

        $model = new Model();
        $model->id = [10, 11, 12, 20];
     
        $errors = [];
        $pass = false;
        try {
            $validator->validate($model);
            $pass = true;
        } catch(\Exception $e) {
            $errors = $e;
        }

        $this->assertSame($pass, true);
    }


    public function testMultipleFilters() {
        $validator = new Validator();
        $cell = new Cells\IdCell();
        $cell->name = "id";
        $cell->min = 1;
        $cell->max = 10000000;
        $validator->id = $cell;


        $cell = new Cells\NumberCell();
        $cell->name = "num";
        $cell->min = 10;
        $cell->max = 20;
        $validator->num = $cell;


        $model = new Model();
        $model->id = [10, 11, 12, 20];
        $model->num = ["min"=>15, "max"=>18];
        $errors = [];
        $pass = false;
        try {
            $validator->validate($model);
            $pass = true;
        } catch(\Exception $e) {
            $errors = $e;
        }

        $this->assertSame($pass, true);
    }
}