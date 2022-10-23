<?php

namespace GenerCodeOrm\Builder;

use GenerCodeOrm\Cells\MetaCell;

class Structure
{
    protected \GenerCodeOrm\Model $model;

    public function __construct(\GenerCodeOrm\Model $model)
    {
        $this->model = $model;
    }


    public function joinIn(
        MetaCell $field,
        MetaCell $right_field,
        bool $inner = true
    ): void {
        $func = (!$inner) ? "leftJoin" : "join";
        $right_table = $right_field->entity->table . " as " . $right_field->entity->alias;
        $this->model->$func($right_table, $field->getDBAlias(), '=', $right_field->getDBAlias());
    }


    public function joinTo($to)
    {
        $ref = "";
        $entity = $this->model->root;
        while ($entity->has("--parent")) {
            $parent = $entity->get("--parent");
            //joined at the same time as loaded, so if exists, it has already been joined
            if (!isset($this->model->entities[$parent->reference])) {
                $entity = $this->model->load($parent->reference, $parent->reference);
                $this->joinIn($parent, $entity->get("--id"));
            }
            if ($parent->reference == $to) {
                break;
            }
        }
        return $entity;
    }


    public function secureTo($profile, $id)
    {
        $top = $this->model->root;
        while ($top->has("--parent")) {
            $parent = $top->get("--parent");
            $top = ($this->model->entity_factory)($parent->reference);
        }

        if ($top->has("--owner")) {
            $owner = $top->get("--owner");
            if ($owner->reference == $profile) {
                $top = $this->joinTo("*");
                $owner = $top->get("--owner"); //get owner with the correct entity reference
                $name = (count($this->model->entities) > 1) ? $owner->getDBAlias() : $owner->name;
                $this->model->where($name, "=", $id);
            }
        }
    }


    public function loadChildren(?array $children = null, $id = null)
    {
        if (!$id) {
            $id = $this->model->root->get("--id");
        }
        foreach ($id->reference as $child) {
            if (!$children or in_array($child, $children)) {
                $entity = $this->model->load($child, $child);
                $this->joinIn($id, $entity->get("--parent"), false);
                $this->loadChildren($children, $entity->get("--id"));
            }
        }
    }

}
