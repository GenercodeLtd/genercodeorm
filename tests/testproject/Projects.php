<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Projects extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("projects");
    
        $cell = new Cell\IdCell();
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->alias = "--id";
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $refs[] = "models";
        $refs[] = "profiles";
        $refs[] = "dictionary-templates";
        $refs[] = "pages";
        $refs[] = "sync-db-log";
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
        $cell->name = "domain";
        $cell->setValidation(0, 100, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "domain";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "hosting_status";
        $cell->setValidation(0, 255, 'active|demo|notactive|cancelled|restricted', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "hosting-status";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "cfdist_id";
        $cell->setValidation(0, 30, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "cfdist-id";
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
    
        $cell = new Cell\StringCell();
        $cell->name = "import_code";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "import-code";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\NumberCell();
        $cell->name = "monthly_price";
        $cell->setValidation(0, 4294967295);
        $cell->round = 2;
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "monthly-price";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\AssetCell();
        $cell->name = "src";
        $cell->dir = "projects/src/";
        $cell->name_template = "projects_src_%id.%ext";
        $cell->setValidation(0, 100000000000, '', '');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "src";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\AssetCell();
        $cell->name = "custom_file";
        $cell->dir = "projects/custom-file/";
        $cell->name_template = "projects_custom_file_%id.%ext";
        $cell->setValidation(0, null, '', '');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "custom-file";
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