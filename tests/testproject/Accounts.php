<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Accounts extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("accounts");
    
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
        $cell->alias = "--owner";
        $cell->name = "user_login_id";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->reference_type = Cell\ReferenceTypes::OWNER;
        $cell->reference = "user-login";
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;

 
        $cell = new Cell\StringCell();
        $cell->name = "username";
        $cell->setValidation(1, 35, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "username";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "password";
        $cell->setValidation(6, 255, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "password";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "company";
        $cell->setValidation(1, 50, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "company";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "firstname";
        $cell->setValidation(0, 30, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "firstname";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "lastname";
        $cell->setValidation(0, 30, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "lastname";
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