<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class States extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("states");
    
        $cell = new Cell\IdCell();
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->alias = "--id";
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
       
        $cell = new Cell\IdCell();
        $cell->name = "models_id";
        $cell->alias = "--parent";
        $cell->reference_type = Cell\ReferenceTypes::PARENT;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $cell->background = true;
        $cell->reference = "models";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
      
        $cell = new Cell\IdCell();
        $cell->name = "archive_id";
        $cell->alias = "--archive";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
       
 
        $cell = new Cell\IdCell();
        $cell->name = "dependant";
        $cell->setValidation(0, 4294967295);
        $cell->reference_type = Cell\ReferenceTypes::REFERENCE;
        $cell->reference = "fields";
        $cell->default = 0;
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "dependant";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "val";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "val";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    

        $cell = new Cell\TimeCell();
        $cell->name = "date_created";
        $cell->alias = "--created";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;


        $cell = new Cell\TimeCell();
        $cell->name = "last_updated";
        $cell->alias = "--updated";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    }
    
}