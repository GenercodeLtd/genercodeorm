<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class UserLogin extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("user_login");
    
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
       
 
        $cell = new Cell\StringCell();
        $cell->name = "username";
        $cell->setValidation(0, 35, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "username";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "password";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->encrypted = true;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "password";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "reset_code";
        $cell->setValidation(0, 75, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "reset-code";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "type";
        $cell->setValidation(0, 45, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "type";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "termsnc";
        $cell->required = true;
 
        $cell->setValidation(1, 1);
        $cell->default = 1;
        $cell->model = $this->model;
        $cell->alias = "termsnc";
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