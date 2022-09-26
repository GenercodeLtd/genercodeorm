<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use \Illuminate\Container\Container as Container;
use \Illuminate\Support\Fluent;

require_once(__DIR__ . "/../app/standardfunctions.php");
\GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app");
\GenerCodeOrm\regAutoload("PressToJam", __DIR__ . "/../../genercodeltd/repos/ptj");

final class FileUploadsTest extends TestCase
{

    private $app;
    private $profile;

    public function setUp() : void {
        $container = new Container();
        $env = new Fluent(["s3bucket"=>"presstojam.com", "s3path"=>"assets"]);
        $configs = require(__DIR__ . "/testproject/configs.php");
        $fluent = new Fluent($configs);
        $container->instance('config', $fluent);

        $container->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });
        $this->app = $container;

        $factory = new PressToJam\ProfileFactory();
        $this->profile = ($factory)("accounts");
        $this->profile->id = 1;
    }

    public function testRun() {
        $_FILES = ["asseter"=> [
            "size"=>500,
            "tmp_name"=>__DIR__ . "/testproject/defaultpdf.pdf",
            "error"=>0,
            "name"=>"defaultpdf.pdf"
        ]];

        $file = $this->app->make(\Illuminate\Filesystem\FilesystemManager::class);
        $prefix = $this->app->config["filesystems.disks.s3"]['prefix_path'];

        $repo = new GenerCodeOrm\SchemaRepository($this->profile->factory);

        $fileUploads = new GenerCodeOrm\FileHandler($file, $repo);
        $fileUploads->name = "tester";
        $params = $fileUploads->processFiles($prefix);
        var_dump($params);
    }

    function testGet() {
        $file = $this->app->make(\Illuminate\Filesystem\FilesystemManager::class);
        $prefix = $this->app->config["filesystems.disks.s3"]['prefix_path'];

        $repo = new GenerCodeOrm\SchemaRepository($this->profile->factory);

        $fileUploads = new GenerCodeOrm\FileHandler($file, $repo);
        echo $fileUploads->get($prefix, "633174b048606.pdf");
      
    }


    function testDelete() {
        $file = $this->app->make(\Illuminate\Filesystem\FilesystemManager::class);
        $prefix = $this->app->config["filesystems.disks.s3"]['prefix_path'];

        $repo = new GenerCodeOrm\SchemaRepository($this->profile->factory);

        $fileUploads = new GenerCodeOrm\FileHandler($file, $repo);
        echo $fileUploads->delete($prefix, "633174b048606.pdf");
      
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