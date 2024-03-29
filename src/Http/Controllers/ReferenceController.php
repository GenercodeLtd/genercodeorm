<?php

namespace GenerCodeOrm\Http\Controllers;

use Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Builder\Builder;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;

class ReferenceController extends AppController
{

    public function bindID(Builder $model, Cells\MetaCell $cell, $id)
    {
        $bind = new Binds\SimpleBind($cell, $id);
        $bind->validate();
        $model->filter($bind);
    }


    public function setParent($model, $cell, $id) : bool
    {
        if ($model->root->has("--parent")) {
            $parent = $model->root->get("--parent");
            if ($parent->reference == $cell->reference) {
                $this->bindID($model, $parent, $id);
                return true;
            }
        }
        return false;
    }

    public function getReferenceEntity($name) : \GenerCodeOrm\Entity
    {
        return (app()->get("entity_factory"))->create($name);
    }


    public function setFields($model, $name) : void
    {
        $recursive = null;
        $aggregator = new Cells\AggregatorStringCell();
        $aggregator->alias = "label";
        foreach ($model->root->cells as $cell) {
            if ($cell->summary) {
                $aggregator->addCell($cell);
            } 
        }
        $model->root->addCell($aggregator);

        $idCell = $model->root->get("--id");
        $idCell->alias = "value";

        $fields = ["value", "--recursive", "label"];
    
        $inputSet = new InputSet($name);
        $inputSet->data($fields);

        $model->fields($inputSet);
    }


    public function setCommon(Builder $model, string $name, $common, string $field, $id) : void
    {
        $crepo = $this->model($name);
        if (!$this->profile->allowedAdminPrivilege($name)) {
            $crepo->secure($this->profile->name, $this->profile->id);
        }

        $crepo->to($common);
        $cell = $crepo->root->get("--id");
        $this->bindID($crepo, $cell, $id);
        $crepo->fields();
        $obj = $crepo->setFromEntity()->take(1)->get()->first();

        $model->to($common);

        $common_id = $common . "/--id";

        $this->bindID($model, $model->getCell("--id", $common), $obj->$common_id);
    }


    public function load($name, $field, $id) : array
    {
        $this->checkPermission($name, "get");
       
        $ref_entity = $this->getReferenceEntity($name);
        $ref_cell = $ref_entity->get($field);

        $model = $this->model($ref_cell->reference);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        if ($ref_cell->common) {
            if (!$this->setParent($model, $ref_entity->get("--parent"), $id)) {
                $parent = $ref_entity->get("--parent");
                $this->setCommon($model, $parent->reference, $ref_cell->common, $field, $id);
            }
        } elseif ($ref_cell->reference_type == Cells\ReferenceTypes::CIRCULAR or $ref_cell->reference_type == Cells\ReferenceTypes::RECURSIVE) {
            $cell = $model->root->get("--parent");
            $this->bindID($model, $cell, $id);
        }

        $this->setFields($model, $ref_cell->reference);
        return $model->setFromEntity()->get()->toArray();
    }
}
