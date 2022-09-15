<?php

namespace PressToJam\Schema;
use \GenerCodeOrm as Core;	
use \GenerCodeOrm\Cells as Cell;
use \GenerCodeOrm\ReferenceDetails;

class Sections extends Core\Schema {

    function __construct($container, $slug = "") {
        parent::__construct($container, $slug, "sections", "sections");
    }
  
    function primary() {
  
        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "id";
        $cell->reference_type = Cell\ReferenceTypes::PRIMARY;
        $cell->slug = "--id";
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $refs = [];
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function parent() {
     
        $cell->alias = $this->alias;
        $cell->name = "models_id";
        $cell->slug = "--parentid";
        $cell->reference_type = Cell\ReferenceTypes::PARENT;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $cell->background = true;
        $cell->reference = new ReferenceDetails("models/", function($slug) { return new Models($this->container, $slug); });
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function archive() {
    
        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "archive_id";
        $cell->slug = "--archive";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function sort() {
    
        $cell = new Cell\NumberCell();
        $cell->alias = $this->alias;
        $cell->name = "_sort";
        $cell->setValidation(0, 65535);
        $cell->slug = "--sort";
        $cell->model = $this->model;
        $cell->system = true;
        $cell->background = true;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function dateCreated() {
      
        $cell = new Cell\TimeCell();
        $cell->alias = $this->alias;
        $cell->name = "date_created";
        $cell->slug = "date-created";
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        $cell->system = true;
        return $cell;
    }

    function lastUpdated() {
    
        $cell = new Cell\TimeCell();
        $cell->name = "last_updated";
        $cell->alias = $this->alias;
        $cell->slug = "last-updated";
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        $cell->system = true;
        return $cell;
    }
 
    function getName() {
 
        $field = new Cell\StringCell();
        $field->name = "name";
        $field->setValidation(1, 70, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->summary = true;
        $field->model = $this->model;
        $field->slug = "name";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getRequired() {
    
        $field = new Cell\FlagCell();
        $field->name = "required";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "required";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    

    function getFromAlias($alias) {
        switch($alias) {
            case '--id':
                return $this->primary();
                break;
            case '--parentid':
                return $this->parent();
                break;
            case '--archive':
                return $this->archive();
                break;
            case '--sort':
                return $this->sort();
                break;
            case 'date-created':
                return $this->dateCreated();
                break;
            case 'last-updated':
                return $this->lastUpdated();
                break;
            case 'name':
                return $this->getName();
                break;
            case 'required':
                return $this->getRequired();
                break;
        }
    }


    function getAllAliases() {
        $arr=[];
        $arr[] = '--id';
        $arr[] = '--parentid';
        $arr[] = 'name';
        $arr[] = 'required';
        $arr[] = 'date-created';
        $arr[] = 'last-updated';
        return $arr;
    }


    function getSummaryAliases() {
        $arr=[];
        $arr[] = 'name';
        return $arr;
    }


    function getSchema() {
        $schema = [];
        $arr = $this->getAllAliases();
        foreach ($arr as $val) {
            $cell = $this->getFromAlias($val);
            $schema[$val] = $cell->toSchema();
        }
        return $schema;
    }
    
}