<?php
namespace GenerCodeOrm\Mappers;

use GenerCodeOrm as Core;

class MapJoins
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
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


    public function buildReferenceJoins(Core\Schema $collection)
    {
        $active_cells = $collection->getActiveCells();
        foreach ($active_cells as $cell) {
            if ($cell->reference_type == Core\Cells\ReferenceTypes::REFERENCE) {
                $ref_collection = $collection->getActiveCollection($cell->reference);
                $ref_col = $ref_collection->getActiveCell("--id");
                $this->buildJoin($collection, $cell, $ref_collection, $ref_col, (bool) $cell->min);
            }
        }
    }


    public function buildUp(Core\Schema $collection)
    {
        $this->buildReferenceJoins($collection);
        if ($collection->has("--parent")) {
            $parent = $collection->get("--parent");
            if ($collection->hasActiveCollection($parent->reference)) {
                $parent_collection = $collection->getActiveCollection($parent->reference);
                $primary = $parent_collection->getActiveCell("--id");
                $this->buildJoin($collection, $parent, $parent_collection, $primary);
                $this->buildUp($collection->getActiveCollection($parent->reference));
            }
        } elseif ($collection->hasActiveCell("--owner")) {
            $owner = $collection->getActiveCell("--owner");
            $this->query->join("users", $collection->table . "." . $owner->name, "=", "users.id");
        }
    }

    public function buildDown(Core\Schema $collection)
    {
        $primary = $collection->getActiveCell("--id");
        foreach ($primary->reference as $child) {
            if ($collection->hasActiveCollection($child)) {
                $child_collection = $collection->getActiveCollection($child);
                $this->buildReferenceJoins($child_collection);
                $child_parent = $child_collection->getActiveCell("--parent");
                $this->buildJoin($collection, $primary, $child_collection, $child_parent, false);
                $this->buildDown($child_collection);
            }
        }
    }
}