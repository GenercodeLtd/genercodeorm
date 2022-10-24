<?php

namespace GenerCodeOrm;

use PressToJam\Schemas as Schema;

use Illuminate\Container\Container;

class ReferenceController extends AppController
{

    public function bindID(Model $model, Cells\MetaCell $cell, $id)
    {
        $bind = new Binds\SimpleBind($cell, $id);
        $bind->validate();
        $model->filter($bind);
    }


    public function setParent($model, $cell, $id) : bool
    {
        if ($model->root->has("--parent")) {
            $parent = $model->root->get("--parent");
            if ($parent->reference == $cell->common) {
                $this->bindID($model, $parent, $id);
                return true;
            }
        }
        return false;
    }

    public function getReferenceCell($name, $field) : Cells\MetaCell
    {
        $entity = ($this->profile->factory)($name);
        return $entity->get($field);
    }


    public function setFields($model, $name) : void
    {
        $recursive = null;
        $aggregator = new Cells\AggregatorStringCell();
        $aggregator->alias = "--value";
        foreach ($model->root->cells as $cell) {
            if ($cell->summary) {
                $aggregator->addCell($cell);
            } 
        }
        $model->root->addCell($aggregator);

        $idCell = $model->root->get("--id");
        $idCell->alias = "--key";

        $fields = ["--key", "--recursive", "--value"];
        echo " Input for " . $name;
        $inputSet = new InputSet($name);
        $inputSet->data($fields);

        $model->fields($inputSet);
    }


    public function setCommon(Model $model, string $name, string $field, $id) : void
    {
        $crepo = $this->model($model);
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $crepo->to($cell->common);
        $cell = $model->root->get("--parent");
        $this->bindID($model, $cell, $id);
        $obj = $crepo->setFromEntity()->take(1)->get()->first();

        $model->to($cell->common);

        $common_id = $cell->common + "/--id";

        $this->bindID($model, $model->getCell("--id", $cell->common), $obj->$common_id);
    }


    public function load($name, $field, $id) : array
    {
        $this->checkPermission($name, "get");
       
        $ref_cell = $this->getReferenceCell($name, $field);

        $model = $this->model($ref_cell->reference);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        if ($ref_cell->common) {
            if (!$this->setParent($model, $ref_cell, $id)) {
                $this->setCommon($model, $name, $field, $id);
            }
        } elseif ($ref_cell->reference_type == Cells\ReferenceTypes::CIRCULAR or $ref_cell->reference_type == Cells\ReferenceTypes::RECURSIVE) {
            $cell = $model->root->get("--parent");
            $this->bindID($model, $cell, $id);
        }

        $this->setFields($model, $ref_cell->reference);
        return $model->setFromEntity()->get()->toArray();
    }
}
