<?php

namespace GenerCodeOrm\Builder;

use GenerCodeOrm\Cells\MetaCell;

trait Structure
{
    

    public function joinIn(
        MetaCell $field,
        MetaCell $right_field,
        bool $inner = true
    ): void {
        $func = (!$inner) ? "leftJoin" : "join";
        $right_table = $right_field->entity->table . " as " . $right_field->entity->alias;
        $this->$func($right_table, $field->getDBAlias(), '=', $right_field->getDBAlias());
    }


    public function joinTo($to, $active = true)
    {
        $ref = "";
        $entity = $this->root;
        while ($entity->has("--parent")) {
            $parent = $entity->get("--parent");
            //joined at the same time as loaded, so if exists, it has already been joined
            if (!isset($this->entities[$parent->reference])) {
                $entity = $this->load($parent->reference, $parent->reference, $active);
                $this->joinIn($parent, $entity->get("--id"));
            } else {
                $entity = $this->entities[$parent->reference];
                if ($active) $this->addActive($parent->reference, $entity);
            }
            if ($parent->reference == $to) {
                break;
            }
        }
        return $entity;
    }


    public function secureTo($profile, $id)
    {
        $top = $this->root;
        while ($top->has("--parent")) {
            $parent = $top->get("--parent");
            $top = ($this->entity_factory)->create($parent->reference);
        }

        if ($top->has("--owner")) {
            $owner = $top->get("--owner");
            if ($owner->reference == $profile) {
                $top = $this->joinTo("*", false);
                $owner = $top->get("--owner"); //get owner with the correct entity reference
                $this->where($owner->getDBAlias(), "=", $id);
            }
        }
    }


    public function loadChildren(?array $children = null, $id = null)
    {
        if (!$id) {
            $id = $this->root->get("--id");
        }
        foreach ($id->reference as $child) {
            if (!$children or in_array($child, $children)) {
                $entity = $this->load($child, $child);
                $this->joinIn($id, $entity->get("--parent"), false);
                $this->loadChildren($children, $entity->get("--id"));
            }
        }
    }

}
