<?php

namespace GenercodeCore\Mappers;

class Map
{

    protected $query;

    public function __construct($table)
    {
        $this->query = DB::table($table);
    }


    private function buildJoin(
        Core\Schema $collection,
        Core\Cells\MetaCell $field,
        Core\Schema $right_collection,
        Core\Cells\MetaCell $right_field,
        bool $inner = true
    ): void
    {
        $left_table = $collection->table;
        $left_id = $left_table . "." . $field->name;
        $right_table = $right_collection->table;
        $right_id = $right_table . "." . $right_field->name;
        if (!$inner) {
            $this->query->leftJoin($right_table, $left_id, '=', $right_id);
        } else {
            $this->query->join($right_table, $left_id, '=', $right_id);
        }
    }


    public function convertName($slug)
    {
        $pos = strrpos($slug, "/");
        if ($pos === false) {
            return ["container"=>"", "alias"=>$slug];
        } else {
            return ["container"=>substr($slug, 0, $pos + 1), "alias"=>substr($slug, $pos + 1)];
        }
    }

    
}
