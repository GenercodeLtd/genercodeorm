<?php

namespace GenerCodeOrm;

use PressToJam\Schemas as Schema;

use Illuminate\Container\Container;

class Reference
{
    protected $app;
    protected $repo;
    protected $connection;
    protected $name = "public";


    public function __construct(\Illuminate\Database\Connection $connection, SchemaRepository $schema)
    {
        $this->repo = $schema;
        $this->connection = $connection;
    }


    public function setRepo(string $name, string $field, $id, Repository $repo)
    {
        $this->repo->loadBase($name);
        $cell = $this->repo->get($field);

        $repo->name = $cell->reference;

        if ($cell->reference_type == Cells\ReferenceTypes::CIRCULAR OR $cell->reference_type == Cells\ReferenceTypes::RECURSIVE) {
            $repo->where = ["--parent"=>$id];
        } else if ($cell->common) {
            if ($this->repo->has("--parent")) {
                $parent = $this->repo->get("--parent");
                if ($parent->reference == $cell->common) {
                    $repo->where = ["--parent"=>$id];
                    return;
                }
            }
            $crepo = new Repository($this->connection, $this->repo);
            $crepo->name = $name;
            $crepo->secure = $repo->secure;
            $crepo->to = $cell->common;
            $crepo->where = ["--parent"=>$id];
            $crepo->limit = 1;
            $obj = $crepo->get();
            $repo->to = $cell->common;
            $common_id = $cell->common + "/--id";
            $repo->where = [$common_id => $obj->$common_id];
        }
    }
}
