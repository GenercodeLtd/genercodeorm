<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class DictionaryTemplates extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("dictionary_templates");
    
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
        $cell->name = "language";
        $cell->setValidation(0, 5, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "language";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\AssetCell();
        $cell->name = "template";
        $cell->dir = "dictionary-templates/template/";
        $cell->name_template = "dictionary_templates_template_%id.%ext";
        $cell->setValidation(0, null, '', '');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "template";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "process";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "process";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "is_default";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "is-default";
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