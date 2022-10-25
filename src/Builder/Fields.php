<?php

namespace GenerCodeOrm\Builder;

class Fields
{
    protected \GenerCodeOrm\Model $model;

    protected $fields = [];

    public function __construct(\GenerCodeOrm\Model $model)
    {
        $this->model = $model;
    }


    protected function addAggregatorStringField($cell)
    {
        $sql = "CONCAT_WS('" . $cell->ws . "', ";
        $sql_fields = [];
        foreach ($cell->cells as $icell) {
            $sql_fields[] = $icell->entity->alias . "." . $icell->name;
        }
        $sql .= implode(", ", $sql_fields);
        $sql .= ") AS '" . $cell->getSlug() . "'";
        $this->model->addSelect($this->model->raw($sql));
    }


    protected function addField($cell)
    {
        $name = (count($this->model->entities) > 1) ? $cell->getDBAlias() : $cell->name;
        $this->model->addSelect($name . " as " . $cell->getSlug());
    }


    protected function selectFields()
    {
        foreach ($this->fields as $cell) {
            if (get_class($cell) == \GenerCodeOrm\Cells\AggregatorStringCell::class) {
                $this->addAggregatorStringField($cell);
            } else {
                $name = (count($this->model->entities) > 1) ? $cell->getDBAlias() : $cell->name;
                $this->model->addSelect($name . " as " . $cell->getSlug());
            }
        }
    }

    protected function selectAllCells($entity, $include_references = true)
    {
        foreach ($entity->cells as $alias=>$cell) {
            $this->fields[] = $cell;
            if ($include_references AND $cell->reference_type == \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE) {
                $this->model->addReference($cell);
            }
        }
    }


    protected function selectCells($entity, \GenerCodeOrm\InputValues $fields, $include_references = true)
    {
        foreach ($entity->cells as $alias=>$cell) {
            if (in_array($cell->alias, $fields->values)) {
                $this->fields[] = $cell;
                if ($include_references AND $cell->reference_type == \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE) {
                    $this->model->addReference($cell);
                }
            }
        }
    }


    public function __invoke(?\GenerCodeOrm\InputSet $fields = null, $include_references = true)
    {
        for ($i=0; $i<count($this->model->active); ++$i) {
            $slug = array_keys($this->model->active)[$i];
            $entity = $this->model->active[$slug];
            if (!$fields) {
                $this->selectAllCells($entity, $include_references);
            } else {
                $cfields = $fields->getValues($slug);
                if ($cfields) {
                    $this->selectCells($entity, $cfields, $include_references);
                }
            }
        }


        $this->selectFields();
        $this->fields = []; //reset so the class can go again.
    }
}
