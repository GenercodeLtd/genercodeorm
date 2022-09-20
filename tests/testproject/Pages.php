<?php

namespace PressToJam\Schemas;
use \GenerCodeOrm\Cells as Cell;


class Pages extends \GenerCodeOrm\Schema {

    protected $cells = [];

    function __construct() {
        parent::__construct("pages");
    
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
        $cell->name = "title";
        $cell->setValidation(1, 250, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "title";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\AssetCell();
        $cell->name = "content";
        $cell->dir = "pages/content/";
        $cell->name_template = "pages_content_%id.%ext";
        $cell->setValidation(0, null, '', '');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "content";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "post_mime_type";
        $cell->setValidation(0, 100, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "post-mime-type";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "post_type";
        $cell->setValidation(0, 20, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "post-type";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\NumberCell();
        $cell->name = "menu_order";
        $cell->setValidation(0, 4294967295);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "menu-order";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "guid";
        $cell->setValidation(0, 255, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "guid";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "post_name";
        $cell->setValidation(1, 200, '', '[<>]+');
        $cell->default = "";
        $cell->summary = true;
        $cell->model = $this->model;
        $cell->alias = "post-name";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "ping_status";
        $cell->setValidation(0, 255, 'open|closed', '[<>]+');
        $cell->default = "open";
        $cell->model = $this->model;
        $cell->alias = "ping-status";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "comment_status";
        $cell->setValidation(0, 255, 'open|closed', '[<>]+');
        $cell->default = "open";
        $cell->model = $this->model;
        $cell->alias = "comment-status";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "status";
        $cell->setValidation(0, 255, 'publish|inherit|draft', '[<>]+');
        $cell->default = "publish";
        $cell->model = $this->model;
        $cell->alias = "status";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\StringCell();
        $cell->name = "excerpt";
        $cell->setValidation(0, 500, '', '[<>]+');
        $cell->default = "";
        $cell->model = $this->model;
        $cell->alias = "excerpt";
        $cell->schema = $this;
        $this->cells[$cell->alias] = $cell;
    
        $cell = new Cell\FlagCell();
        $cell->name = "publish";
 
        $cell->setValidation(0, 1);
        $cell->default = 0;
        $cell->model = $this->model;
        $cell->alias = "publish";
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