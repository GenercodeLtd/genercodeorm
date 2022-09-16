<?php

namespace GenerCode\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Sections extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct($slug = "") {
        parent::__construct($slug, "sections", "sections");

        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->alias = "--id";
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "models_id";
        $cell->alias = "--parent";
        $cell->reference_type = Cell\ReferenceTypes::PARENT;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $cell->background = true;
        $cell->reference = "models";
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "archive_id";
        $cell->alias = "--archive";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\NumberCell();
        $cell->alias = $this->alias;
        $cell->name = "_sort";
        $cell->setValidation(0, 65535);
        $cell->alias = "--sort";
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\TimeCell();
        $cell->alias = $this->alias;
        $cell->name = "date_created";
        $cell->alias = "--created";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $this->cells[$cell->alias] = $cell;


        $cell = new Cell\TimeCell();
        $cell->name = "last_updated";
        $cell->alias = $this->alias;
        $cell->alias = "--updated";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "name";
        $cell->setValidation(1, 70, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "name";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\FlagCell();
        $cell->name = "required";

        $cell->setValidation(0, 1);
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "required";
        $this->cells[$cell->alias] = $cell;

    }

}
