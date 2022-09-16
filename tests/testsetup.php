<?php


require_once(__DIR__ . "/../app/standardfunctions.php");
/*$container = new Container();
$capsule = new Capsule($container);
$capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'test_database',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        
$capsule->setAsGlobal();*/

GenerCodeOrm\regAutoload("GenerCodeOrm", __DIR__ . "/../app/");