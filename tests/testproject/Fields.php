<?php

namespace PressToJam\Schema;
use \GenerCodeOrm as Core;	
use \GenerCodeOrm\Cells as Cell;
use \GenerCodeOrm\ReferenceDetails;

class Fields extends Core\Schema {

    function __construct($container, $slug = "") {
        parent::__construct($container, $slug, "fields", "fields");
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
        $cell = new Cell\IdCell();
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

    function recursiveId() {
        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "_recursive_id";
        $cell->is_recursive = true;
        $cell->slug = "--recursive-id";
        $cell->setValidation(0, 18446744073709551615);
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        $cell->system = true;
        $cell->background = true;
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
        $field->setValidation(1, 50, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->summary = true;
        $field->model = $this->model;
        $field->slug = "name";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getType() {
        $field = new Cell\StringCell();
        $field->name = "type";
        $field->setValidation(1, 255, 'str|number|time|asset|flag|id', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "type";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getDefaultVal() {
        $field = new Cell\StringCell();
        $field->name = "default_val";
        $field->setValidation(0, 200, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "default-val";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getMin() {
        $field = new Cell\NumberCell();
        $field->name = "min";
        $field->setValidation(0, 4294967295);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "min";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getMax() {
        $field->name = "max";
        $field->setValidation(0, 4294967295);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "max";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getContains() {
        $field = new Cell\StringCell();
        $field->name = "contains";
        $field->setValidation(0, 250, '', '');
        $field->alias = $this->alias;
        $field->default = "";
        $states = [];
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "id";
        $sfield = new Cell\IdCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 4294967295);
        $sfield->reference = new Models($this->slug . "/");
        $sfield->alias = $this->alias;
        $sfield->default = 0;
        $state->field = $sfield;
        $states[] = $state;
        $state = new Cell\State();
        $state->depends_on = "type";
        $state->depends_val = "";
        $sfield = new Cell\StringCell();
        $sfield->name = "contains";
        $sfield->setValidation(0, 250, '', '[<>]+');
        $sfield->alias = $this->alias;
        $sfield->default = "";
        $state->field = $sfield;
        $states[] = $state;
        $field->states = $states;
        $field->model = $this->model;
        $field->slug = "contains";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getNotcontains() {
        $field = new Cell\StringCell();
        $field->name = "notcontains";
        $field->setValidation(0, 250, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "notcontains";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSummary() {
        $field = new Cell\FlagCell();
        $field->name = "summary";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "summary";
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

    
    function getIsUnique() {
        $field = new Cell\FlagCell();
        $field->name = "is_unique";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "is-unique";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSystem() {
  
        $field = new Cell\FlagCell();
        $field->name = "system";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "system";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getConstant() {
 
        $field = new Cell\FlagCell();
        $field->name = "constant";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "constant";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getCalculated() {
  
        $field = new Cell\FlagCell();
        $field->name = "calculated";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "calculated";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSectionId() {
  
        $field = new Cell\IdCell();
        $field->name = "section_id";
        $field->setValidation(0, 4294967295);
        $field->reference_type = \GenerCodeOrm\Cells\ReferenceTypes::REFERENCE;
        $field->reference = new ReferenceDetails($this->slug . "section-id/", function($slug) { return new Sections($this->container, $slug); });
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "section-id";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getDependsOn() {
 
        $field = new Cell\IdCell();
        $field->name = "depends_on";
        $field->setValidation(0, 4294967295);
        $field->reference = new Fields($this->slug . "depends-on/");
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "depends-on";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getDependsVal() {
  
        $field = new Cell\StringCell();
        $field->name = "depends_val";
        $field->setValidation(0, 255, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "depends-val";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    

    function getFromAlias($alias) {
        switch($alias) {
            case '--id':
                return $this->primary();
                break;
            case '--parent':
                return $this->parent();
                break;
            case '--archive':
                return $this->archive();
                break;
            case '--sort':
                return $this->sort();
                break;
            case '--recursive':
                return $this->recursiveId();
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
            case 'type':
                return $this->getType();
                break;
            case 'default-val':
                return $this->getDefaultVal();
                break;
            case 'min':
                return $this->getMin();
                break;
            case 'max':
                return $this->getMax();
                break;
            case 'contains':
                return $this->getContains();
                break;
            case 'notcontains':
                return $this->getNotcontains();
                break;
            case 'summary':
                return $this->getSummary();
                break;
            case 'required':
                return $this->getRequired();
                break;
            case 'is-unique':
                return $this->getIsUnique();
                break;
            case 'system':
                return $this->getSystem();
                break;
            case 'constant':
                return $this->getConstant();
                break;
            case 'calculated':
                return $this->getCalculated();
                break;
            case 'section-id':
                return $this->getSectionId();
                break;
            case 'depends-on':
                return $this->getDependsOn();
                break;
            case 'depends-val':
                return $this->getDependsVal();
                break;
        }
    }


    function getAllAliases() {
        $arr=[];
        $arr[] = '--id';
        $arr[] = '--parent';
        $arr[] = "--recursive";
        $arr[] = 'name';
        $arr[] = 'type';
        $arr[] = 'default-val';
        $arr[] = 'min';
        $arr[] = 'max';
        $arr[] = 'contains';
        $arr[] = 'notcontains';
        $arr[] = 'summary';
        $arr[] = 'required';
        $arr[] = 'is-unique';
        $arr[] = 'system';
        $arr[] = 'constant';
        $arr[] = 'calculated';
        $arr[] = 'section-id';
        $arr[] = 'depends-on';
        $arr[] = 'depends-val';
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