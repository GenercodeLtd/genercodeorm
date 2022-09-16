<?php

namespace GenerCode\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Fields extends \GenerCodeOrm\Schema {


    function __construct($slug = "") {
        parent::__construct($slug, "fields", "fields");

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
        echo "\nSlug is " . $cell->alias;
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

        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "_recursive_id";
        $cell->is_recursive = true;
        $cell->alias = "--recursive";
        $cell->setValidation(0, 18446744073709551615);
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
        $cell->setValidation(1, 50, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "name";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "number_type";
        $cell->setValidation(0, 255, 'numbers|currency', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "number-type";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "format";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "format";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\IdCell();
        $cell->name = "references";
        $cell->setValidation(0, 4294967295);
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "references";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "file_type";
        $cell->setValidation(0, 255, 'img|video|audio|pdf|document', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "file-type";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "type";
        $cell->setValidation(1, 255, 'str|number|time|asset|flag|id', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "type";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "default_val";
        $cell->setValidation(0, 200, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "default-val";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\NumberCell();
        $cell->name = "min";
        $cell->setValidation(0, 4294967295);
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "min";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\NumberCell();
        $cell->name = "max";
        $cell->setValidation(0, 4294967295);
        $cell->alias = $this->alias;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "max";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "contains";
        $cell->setValidation(0, 250, '', '');
        $cell->alias = $this->alias;
        $cell->default = "";
        $states = [];
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "id";
        $sfield = new Cell\IdCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 4294967295);
        $sfield->reference = new Models($this->slug . "/");
        $sfield->alias = $this->alias;
        $sfield->default = 0;
        $state->field = $sfield;
        $states[] = $state;
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "";
        $sfield = new Cell\StringCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 250, '', '[<>]+');
        $sfield->alias = $this->alias;
        $sfield->default = "";
        $state->field = $sfield;
        $states[] = $state;
        $cell->states = $states;
        $cell->model = $this->model;
        $cell->alias = "contains";
        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\StringCell();
        $cell->name = "notcontains";
        $cell->setValidation(0, 250, '', '[<>]+');
        $cell->alias = $this->alias;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "notcontains";
        $this->cells[$cell->alias] = $cell;
    }
}