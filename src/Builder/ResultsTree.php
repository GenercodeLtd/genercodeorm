<?php

namespace GenerCodeOrm\Builder;

class ResultsTree
{
    protected $entities;

    protected $children = [];

    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }


    public function toTree($parent, $row, \GenerCodeOrm\Entity $entity)
    {
        if (!property_exists($parent, $entity->slug)) {
            $parent->{$entity->slug} = [];
        }

        $id = $row->{$entity->slug . "/--id"};
        if (!isset($parent->{ $entity->slug }[$id])) {
            $obj = new \StdClass();
            foreach ($entity->cells as $alias=>$cell) {
                if (property_exists($row, $entity->slug . "/" . $alias)) {
                    $obj->$alias = $row->{$entity->slug . "/" . $alias};
                }
            }
            $parent->{ $entity->slug }[$id] = $obj;
        }
        $parent_obj = $parent->{ $entity->slug }[$id];

        $idCell = $entity->get("--id");

        foreach ($idCell->reference as $alias) {
            if (isset($this->entities[$alias])) {
                $this->toTree($parent_obj, $row, $this->entities[$alias]);
            }
        }
    }

    //still need to fold results into tree
    public function __invoke($parent, $row, $entity)
    {
        $this->toTree($parent, $row, $entity);
    }
}
