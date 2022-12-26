<?php
namespace PressToJam\Entity;
use \GenerCodeOrm\Cells as Cell;


class PrimaryBusiness extends \GenerCodeOrm\Entity {

    protected $cells = [];

    function __construct() {
        parent::__construct("primary_business");
 
        $this->has_import = true;
    
        $cell = new Cell\IdCell();
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->alias = "--id";
        $cell->system = true;
        $cell->setValidation(1);
        $refs = [];
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->entity = $this;
        $this->cells[$cell->alias] = $cell;


 
        $cell = new Cell\StringCell();
        $cell->name = "company_type";
        $cell->setValidation(200, null);
     
        $cell->list = [];
 
        $cell->summary = true;
 
        $cell->model = $this->model;
        $cell->alias = "company-type";
        $cell->entity = $this;

        $this->cells[$cell->alias] = $cell;

        $cell = new Cell\TimeCell();
        $cell->name = "date_created";
        $cell->alias = "--created";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->entity = $this;
        $this->cells[$cell->alias] = $cell;


        $cell = new Cell\TimeCell();
        $cell->name = "last_updated";
        $cell->alias = "--updated";
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->entity = $this;
        $this->cells[$cell->alias] = $cell;
    }
    
}