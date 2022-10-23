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
        $sql = "CONCAT(";
        if ($cell->ws) {
            $sql .= $cell->ws . ",";
        }
        $sql_fields = [];
        foreach ($cell->cells as $cell) {
            $sql_fields[] = $cell->entity->alias . "." . $cell->name;
        }
        $sql .= implode(", ", $sql_fields);
        $sql .= ") AS '" . $cell->getSlug() . "'";
        $this->model->addSelect($this->raw($sql));
    }


    protected function addField($cell)
    {
        $name = (count($this->model->entities) > 1) ? $cell->getDBAlias() : $cell->name;
        $this->model->addSelect($name . " as " . $cell->getSlug());
    }


    protected function selectFields() {
        foreach($this->fields as $cell) {
            if (get_class($cell) == \GenerCodeOrm\Cells\AggregatorStringCell::class) {
                $this->addAggregatorStringField($cell);
            } else {
                $name = (count($this->model->entities) > 1) ? $cell->getDBAlias() : $cell->name;
                $this->model->addSelect($name . " as " . $cell->getSlug());
            }
        }
    }



    protected function selectCells($slug, $entity, ?array $fields = null)
    {
        foreach ($entity->cells as $alias=>$cell) {
            if (!$fields or in_array($cell->alias, $fields)) {
                $this->fields[] = $cell;
                if ($cell->reference_type == \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE) {
                    $this->model->addReference($cell);
                }
            }
        }
    }


    public function __invoke($fields = null)
    {
        foreach ($this->model->entities as $slug=>$entity) {
            $this->selectCells($slug, $entity, $fields);
        }

        $this->selectFields();
        $this->fields = []; //reset so the class can go again.
    }
}
