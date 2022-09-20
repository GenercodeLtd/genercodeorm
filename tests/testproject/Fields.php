<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Fields extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("fields");
    
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
       
        $cell = new Cell\NumberCell();
        $cell->name = "_sort";
        $cell->setValidation(0, 65535);
        $cell->alias = "--sort";
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
       
        $cell = new Cell\IdCell();
        $cell->name = "_recursive_id";
        $cell->reference_type = Cell\ReferenceTypes::RECURSIVE;
        $cell->alias = "--recursive";
        $cell->setValidation(0, 18446744073709551615);
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
       

 
        $cell = new Cell\StringCell();
        $cell->name = "name";
        $cell->setValidation(1, 50, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "name";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "type";
        $cell->setValidation(1, 255, 'str|number|time|asset|flag|id|json', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "type";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "default_val";
        $cell->setValidation(0, 200, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "default-val";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\NumberCell();
        $cell->name = "min";
        $cell->setValidation(0, 4294967295);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "min";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\NumberCell();
        $cell->name = "max";
        $cell->setValidation(0, 4294967295);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "max";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "contains";
        $cell->setValidation(0, 250, '', '');
        $cell->default = "";
        $states = [];
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "id";
        $sfield = new Cell\IdCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 4294967295);
        $sfield->reference_type = Cell\ReferenceTypes::REFERENCE;
        $sfield->reference = "models";
        $sfield->default = 0;
        $state->field = $sfield;
        $states[] = $state;
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "";
        $sfield = new Cell\StringCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 250, '', '[<>]+');
        $sfield->default = "";
        $state->field = $sfield;
        $states[] = $state;
        $cell->states = $states;
        $cell->model = $this->model;
        $cell->alias = "contains";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "notcontains";
        $cell->setValidation(0, 250, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "notcontains";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "summary";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "summary";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "required";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "required";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "is_unique";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "is-unique";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "is_system";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "is-system";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "constant";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "constant";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "calculated";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "calculated";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\IdCell();
        $cell->name = "section_id";
        $cell->setValidation(0, 4294967295);
        $cell->reference_type = Cell\ReferenceTypes::REFERENCE;
        $cell->reference = "sections";
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "section-id";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\IdCell();
        $cell->name = "depends_on";
        $cell->setValidation(0, 4294967295);
        $cell->reference_type = Cell\ReferenceTypes::REFERENCE;
        $cell->reference = "fields";
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "depends-on";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "depends_val";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "depends-val";
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