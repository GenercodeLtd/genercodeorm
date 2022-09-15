<?php

namespace PressToJam\Schema;
use \GenerCodeOrm as Core;	
use \GenerCodeOrm\Cells as Cell;
use \GenerCodeOrm\ReferenceDetails;

class Models extends Core\Schema {

    function __construct($container, $slug = "") {
        parent::__construct($container, $slug, "models", "models");
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
        $refs["fields"] = new ReferenceDetails("fields/", function($slug) { return new Fields($this->container, $slug); });
        $cell->reference = $refs;
        $cell->immutable = true;
        $cell->model = $this->model;
        $this->field_cache[$cell->slug] = $cell;
        return $cell;
    }

    function parent() {
   
        $cell = new Cell\IdCell();
        $cell->alias = $this->alias;
        $cell->name = "projects_id";
        $cell->slug = "--parentid";
        $cell->reference_type = Cell\ReferenceTypes::PARENT;
        $cell->system = true;
        $cell->setValidation(1, 18446744073709551615);
        $cell->background = true;
        $cell->reference = new ReferenceDetails("projects/", function($slug) { return new Projects($this->container, $slug); });
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

    
    
    function getPrimaryKey() {
   
        $field = new Cell\StringCell();
        $field->name = "primary_key";
        $field->setValidation(0, 75, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "primary-key";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getParentKey() {
   
        $field = new Cell\StringCell();
        $field->name = "parent_key";
        $field->setValidation(0, 75, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "parent-key";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSoftParent() {

        $field = new Cell\FlagCell();
        $field->name = "soft_parent";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "soft-parent";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getIsRecursive() {
  
        $field = new Cell\FlagCell();
        $field->name = "is_recursive";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "is-recursive";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getIsConstant() {
   
        $field = new Cell\FlagCell();
        $field->name = "is_constant";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "is-constant";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getAudit() {
  
        $field = new Cell\FlagCell();
        $field->name = "audit";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "audit";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getSort() {
   
        $field = new Cell\FlagCell();
        $field->name = "sort";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "sort";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getArchive() {
   
        $field = new Cell\FlagCell();
        $field->name = "archive";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "archive";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPostHooks() {
      
        $field = new Cell\FlagCell();
        $field->name = "post_hooks";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "post-hooks";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPutHooks() {
      
        $field = new Cell\FlagCell();
        $field->name = "put_hooks";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "put-hooks";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getDelHooks() {
   
        $field = new Cell\FlagCell();
        $field->name = "del_hooks";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "del-hooks";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getHasImport() {
    
        $field = new Cell\FlagCell();
        $field->name = "has_import";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "has-import";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getHasExport() {
   
        $field = new Cell\FlagCell();
        $field->name = "has_export";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "has-export";
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
            case 'has-profile-owner':
                return $this->getHasProfileOwner();
                break;
            case 'primary-key':
                return $this->getPrimaryKey();
                break;
            case 'parent-key':
                return $this->getParentKey();
                break;
            case 'soft-parent':
                return $this->getSoftParent();
                break;
            case 'is-recursive':
                return $this->getIsRecursive();
                break;
            case 'is-constant':
                return $this->getIsConstant();
                break;
            case 'audit':
                return $this->getAudit();
                break;
            case 'sort':
                return $this->getSort();
                break;
            case 'archive':
                return $this->getArchive();
                break;
            case 'post-hooks':
                return $this->getPostHooks();
                break;
            case 'put-hooks':
                return $this->getPutHooks();
                break;
            case 'del-hooks':
                return $this->getDelHooks();
                break;
            case 'has-import':
                return $this->getHasImport();
                break;
            case 'has-export':
                return $this->getHasExport();
                break;
        }
    }


    function getAllAliases() {
        $arr=[];
        $arr[] = '--id';
        $arr[] = '--parent';
        $arr[] = "--recursive";
        $arr[] = 'name';
        $arr[] = 'has-profile-owner';
        $arr[] = 'primary-key';
        $arr[] = 'parent-key';
        $arr[] = 'soft-parent';
        $arr[] = 'is-recursive';
        $arr[] = 'is-constant';
        $arr[] = 'audit';
        $arr[] = 'sort';
        $arr[] = 'archive';
        $arr[] = 'post-hooks';
        $arr[] = 'put-hooks';
        $arr[] = 'del-hooks';
        $arr[] = 'has-import';
        $arr[] = 'has-export';
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