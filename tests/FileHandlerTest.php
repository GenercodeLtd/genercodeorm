<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use \Illuminate\Container\Container as Container;
use \Illuminate\Support\Fluent;


final class FileHandlerTest extends TestCase
{

    private $app;
    private $profile;
    private $asset;

    public function setUp() : void {
        $container = new Container();
        $env = new Fluent(["s3bucket"=>"presstojam.com", "s3path"=>"assets"]);
        $configs = require(__DIR__ . "/testproject/configs.php");
        $fluent = new Fluent($configs);
        $container->instance('config', $fluent);

        $container->bind(\Illuminate\Filesystem\FilesystemManager::class, function($app) {
            return new \Illuminate\Filesystem\FilesystemManager($app);
        });


        $container->bind(\GenerCodeOrm\FileHandler::class, function($app) {
            $file = $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            $disk = $file->disk("s3");
            $fileHandler = new \GenerCodeOrm\FileHandler($disk);
            return $fileHandler;
        });
        $this->app = $container;

    }


  

    public function testCreate() {
        $_FILES = ["asseter"=> [
            "size"=>500,
            "tmp_name"=>__DIR__ . "/testproject/defaultpdf.pdf",
            "error"=>0,
            "name"=>"defaultpdf.pdf"
        ]];

        $fcell = new \GenerCodeOrm\Cells\AssetCell();
        $fcell->name = "asseter";
        $fcell->entity = new \GenerCodeOrm\Entity("tester");
        

        $fileHandler = $this->app->make(\GenerCodeOrm\FileHandler::class);

        $bind = new \GenerCodeOrm\Binds\AssetBind($fcell, $_FILES["asseter"]);
        $file_name = $fileHandler->uploadFile($bind);
        $this->assertNotSame("", $file_name);
        $this->asset = $file_name;
        echo "\nFILE NAME IS " . $this->asset;
    }


    function testGet() {
        $this->asset = "assets/tester/63541d11e5440.pdf";
        $fileHandler = $this->app->make(\GenerCodeOrm\FileHandler::class);
        $str = $fileHandler->get($this->asset);
        $this->assertNotSame("", $str);
      
    }


    function testDelete() {
        $this->asset = "assets/tester/63541d11e5440.pdf";
        $fileHandler = $this->app->make(\GenerCodeOrm\FileHandler::class);
        $res = $fileHandler->delete($this->asset);
        $this->assertSame($res, true);
      
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