<?php
namespace GenerCodeOrm;
use PressToJam\Schemas as Schema;

use \Illuminate\Container\Container;

class Reference {

    protected $app;
    protected $repo;
    protected $connection;
    protected $name = "public";
    protected $secure;

    public function __construct(Container $app, \Illuminate\Database\DatabaseManager $dbmanager, SchemaRepository $schema)
    {
        $this->app = $app;
        $this->repo = $schema;
        $this->connection = $dbmanager->connection();
    }


    function setRepo(string $name, string $field, $id, Repository $repo) {
        $this->repo->loadBase($name, "");
        $cell = $this->repo->get($field);

        $repo->name = $cell->reference;

        if ($cell->common) {
            $parent = $this->repo->has("--parent"); //must have parent
            if ($cell->common == $parent->reference) {
                $repo->where = ["--parent"=>$id];
            }
        } else {
            $crepo = $this->app->make(Repository::class);
            $crepo->name = $name;
            $crepo->to = $cell->common;
            $crepo->where = ["--parent"=>$id];
            $crepo->limit = 1;
            $obj = $crepo->get();
            $repo->to = $cell->common;
            $repo->where = [$cell->common + "/--id" => $obj->{ $cell->common + "/--id"}];
        }
    }

 
    

}