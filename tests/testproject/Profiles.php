<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Profiles extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("profiles");
    
        $cell = new Cell\IdCell();
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->alias = "--id";
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $refs[] = "route-flow";
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
       
        $cell = new Cell\IdCell();
        $cell->name = "projects_id";
        $cell->alias = "--parent";
        $cell->reference_type = Cell\ReferenceTypes::PARENT;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $cell->background = true;
        $cell->reference = "projects";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
      
 
        $cell = new Cell\StringCell();
        $cell->name = "name";
        $cell->setValidation(1, 30, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "name";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "anonymous";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "anonymous";
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