<?php

namespace PressToJam\Schema;
use \GenerCodeOrm as Core;	
use \GenerCodeOrm\Cells as Cell;
use \GenerCodeOrm\ReferenceDetails;

class Projects extends Core\Schema {

    function __construct($container, $slug = "") {
        parent::__construct($container, $slug, "projects", "projects");
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
        $refs["models"] = new ReferenceDetails("models/", function($slug) { return new Models($this->container, $slug); });
        $refs["pages"] = new ReferenceDetails("pages/", function($slug) { return new Pages($this->container, $slug); });
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function owner() {
    
        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->slug = "--owner";
        $cell->name = "user_login_id";
        $cell->setValidation(1, 18446744073709551615);
        $cell->immutable = true;
        $cell->is_owner = true;
        $cell->reference = new UserLogin("user-login/");
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
 
    function getDomain() {
    
        $field = new Cell\StringCell();
        $field->name = "domain";
        $field->setValidation(0, 100, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->summary = true;
        $field->model = $this->model;
        $field->slug = "domain";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getHostingStatus() {
     
        $field = new Cell\StringCell();
        $field->name = "hosting_status";
        $field->setValidation(0, 255, 'active|demo|notactive|cancelled|restricted', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "hosting-status";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getCfdistId() {
    
        $field = new Cell\StringCell();
        $field->name = "cfdist_id";
        $field->setValidation(0, 30, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "cfdist-id";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getTermsnc() {
     
        $field = new Cell\FlagCell();
        $field->name = "termsnc";
        $field->required = true;
 
        $field->setValidation(1, 1);
        $field->alias = $this->alias;
        $field->default = 1;
        $field->model = $this->model;
        $field->slug = "termsnc";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getImportCode() {
       
        $field = new Cell\StringCell();
        $field->name = "import_code";
        $field->setValidation(0, 255, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "import-code";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getMonthlyPrice() {
       
        $field = new Cell\NumberCell();
        $field->name = "monthly_price";
        $field->setValidation(0, 4294967295);
        $field->round = 2;
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "monthly-price";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSrc() {
      
        $field = new Cell\AssetCell();
        $field->name = "src";
        $field->dir = "projects/src/";
        $field->name_template = "projects_src_%id.%ext";
        $field->setValidation(0, null, '', '');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "src";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getCustomFile() {
      
        $field = new Cell\AssetCell();
        $field->name = "custom_file";
        $field->dir = "projects/custom-file/";
        $field->name_template = "projects_custom_file_%id.%ext";
        $field->setValidation(0, null, '', '');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "custom-file";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getProcess() {
       
        $field = new Cell\FlagCell();
        $field->name = "process";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "process";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    

    function getFromAlias($alias) {
        switch($alias) {
            case '--id':
                return $this->primary();
                break;
            case '--owner':
                return $this->owner();
                break;
            case 'date-created':
                return $this->dateCreated();
                break;
            case 'last-updated':
                return $this->lastUpdated();
                break;
            case 'domain':
                return $this->getDomain();
                break;
            case 'hosting-status':
                return $this->getHostingStatus();
                break;
            case 'cfdist-id':
                return $this->getCfdistId();
                break;
            case 'termsnc':
                return $this->getTermsnc();
                break;
            case 'import-code':
                return $this->getImportCode();
                break;
            case 'monthly-price':
                return $this->getMonthlyPrice();
                break;
            case 'src':
                return $this->getSrc();
                break;
            case 'custom-file':
                return $this->getCustomFile();
                break;
            case 'process':
                return $this->getProcess();
                break;
        }
    }


    function getAllAliases() {
        $arr=[];
        $arr[] = '--id';
        $arr[] = '--owner';
        $arr[] = 'domain';
        $arr[] = 'hosting-status';
        $arr[] = 'cfdist-id';
        $arr[] = 'termsnc';
        $arr[] = 'import-code';
        $arr[] = 'monthly-price';
        $arr[] = 'src';
        $arr[] = 'custom-file';
        $arr[] = 'process';
        $arr[] = 'date-created';
        $arr[] = 'last-updated';
        return $arr;
    }


    function getSummaryAliases() {
        $arr=[];
        $arr[] = 'domain';
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