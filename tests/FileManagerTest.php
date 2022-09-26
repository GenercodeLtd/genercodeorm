<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use GenerCodeOrm\Cells\MetaCell;
use GenerCodeOrm\Cells\ValidationRules;

use \Illuminate\Support\Fluent;
use \Illuminate\FileSystem\FileSystemManager;

final class FileManagerTest extends TestCase
{

    private $configs;

    public function setUp() : void {
        $this->configs = ["config"=>new Fluent([
        "filesystems.default" => ["driver"=>"s3"],
        "filesystems.disks.s3" => [
            'driver' => 's3',
            'region' => "eu-west-1",
            'bucket' => "presstojamassets"
        ]])];
    }


    function testGetAsset() {
        $src = "presstojam.com/assets/dictionary/template_1.json";
        $file = new \Illuminate\Filesystem\FilesystemManager($this->configs);
        $contents = $file->disk('s3')->get($src);
        var_dump($contents);
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