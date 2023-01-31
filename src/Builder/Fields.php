<?php

namespace GenerCodeOrm\Builder;

trait Fields
{
  
    private function addAggregatorStringField($cell)
    {
        $sql = "CONCAT_WS('" . $cell->ws . "', ";
        $sql_fields = [];
        foreach ($cell->cells as $icell) {
            if (count($this->entities) > 1) $sql_fields[] = $icell->entity->alias . "." . $icell->name;
            else $sql_fields[] = $icell->name;
        }
        $sql .= implode(", ", $sql_fields);
        $sql .= ") AS '" . $cell->getSlug() . "'";
        $this->addSelect($this->raw($sql));
    }


    private function addAggregateField($cell, $aggregate)
    {
        $allowed_aggs = ["sum", "min", "max", "avg"];
        if (!in_array(strtolower($aggregate), $allowed_aggs)) {
            throw new \Exception("Aggregate value must be one of sum, min, max, avg. " . $aggregate . " given");
        }

        $sql = $aggregate . "('" . $cell->getDBAlias() . ") as " . $cell->getSlug() . ")";
        $this->addSelect($this->raw($sql));
    }


    private function addField($cell)
    {
        $this->addSelect($cell->getDBAlias() . " as " . $cell->getSlug());
    }


    private function applyCell($cell, ?\GenerCodeOrm\InputValues $aggregates = null) {
        if (isset($aggregates->values[$cell->alias])) {
            //this is an aggregate value
            $this->addAggregateField($cell, $aggregates->values[$cell->alias]);
        } else if (get_class($cell) == \GenerCodeOrm\Cells\AggregatorStringCell::class) {
            $this->addAggregatorStringField($cell);
        } else {
            $this->addSelect($cell->getDBAlias() . " as " . $cell->getSlug());
        }
    }


    private function selectAllCells($entity, $include_references = true, ?\GenerCodeOrm\InputValues $aggregates = null)
    {
        foreach ($entity->cells as $alias=>$cell) {
            $this->applyCell($cell, $aggregates);
            if ($include_references AND $cell->reference_type == \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE) {
                $this->addReference($cell);
            }
        }
    }


    private function selectCells($entity, \GenerCodeOrm\InputValues $fields, $include_references = true, ?\GenerCodeOrm\InputValues $aggregates = null)
    {
        foreach ($entity->cells as $alias=>$cell) {
            if (in_array($cell->alias, $fields->values)) {

                $this->applyCell($cell, $aggregates);

                if ($include_references AND $cell->reference_type == \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE) {
                    $this->addReference($cell);
                }
            }
        }
    }


    public function fields(?\GenerCodeOrm\InputSet $fields = null, $include_references = true, ?\GenerCodeOrm\InputValues $aggregates = null)
    {
        for ($i=0; $i<count($this->active); ++$i) {
            $slug = array_keys($this->active)[$i];
            $entity = $this->active[$slug];
            if (!$fields) {
                $this->selectAllCells($entity, $include_references, $aggregates);
            } else {
                $cfields = $fields->getValues($slug);
                if ($cfields) {
                    $this->selectCells($entity, $cfields, $include_references, $aggregates);
                }
            }
        }
    }
}
