<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class AccessorTest extends TestCase
{


    function testGet() {
        $accessor = new \GenerCodeOrm\Accessor(["trait"=>17,"gong/trait"=>3, "gong/trait/pong"=>4, "gong/pong"=>5, "gong/pong/trait"=> 6]);
        $this->assertSame($accessor->gong->trait, 3);
        $this->assertSame($accessor->trait, 17);
    }
}