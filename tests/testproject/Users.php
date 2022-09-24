<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Users extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("users");
    
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
        $cell->name = "name";
        $cell->setValidation(1, 255, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "name";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "email";
        $cell->setValidation(1, 255, '', '[<>]+');
        $cell->unique = true;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "email";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\TimeCell();
        $cell->name = "email_verified_at";
        $cell->format = "";
        $cell->setValidation(0, 255);
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "email-verified-at";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "password";
        $cell->setValidation(1, 255, '', '[<>]+');
        $cell->encrypted = true;
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "password";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "remember_token";
        $cell->setValidation(0, 100, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "remember-token";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "terms";
        $cell->required = true;
 
        $cell->setValidation(1, 1);
        $cell->default = 1;
        $cell->model = $this->model;
        $cell->alias = "terms";
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