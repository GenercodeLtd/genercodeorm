<?php

namespace PressToJam\Schema;
use \GenerCodeOrm as Core;	
use \GenerCodeOrm\Cells as Cell;
use \GenerCodeOrm\ReferenceDetails as ReferenceDetails;

class Pages extends Core\Schema {

    function __construct($container, $slug = "") {
        parent::__construct($container, $slug, "pages", "pages");
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
 
    function getTitle() {
 
        $field = new Cell\StringCell();
        $field->name = "title";
        $field->setValidation(1, 250, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->summary = true;
        $field->model = $this->model;
        $field->slug = "title";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getContent() {
     
        $field = new Cell\AssetCell();
        $field->name = "content";
        $field->dir = "pages/content/";
        $field->name_template = "pages_content_%id.%ext";
        $field->setValidation(0, null, '', '');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "content";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPostMimeType() {
    
        $field = new Cell\StringCell();
        $field->name = "post_mime_type";
        $field->setValidation(0, 100, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "post-mime-type";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPostType() {
 
        $field = new Cell\StringCell();
        $field->name = "post_type";
        $field->setValidation(0, 20, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "post-type";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getMenuOrder() {
  
        $field = new Cell\NumberCell();
        $field->name = "menu_order";
        $field->setValidation(0, 4294967295);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "menu-order";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getGuid() {
  
        $field = new Cell\StringCell();
        $field->name = "guid";
        $field->setValidation(0, 255, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "guid";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPostName() {
 
        $field = new Cell\StringCell();
        $field->name = "post_name";
        $field->setValidation(1, 200, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->summary = true;
        $field->model = $this->model;
        $field->slug = "post-name";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPingStatus() {
   
        $field = new Cell\StringCell();
        $field->name = "ping_status";
        $field->setValidation(0, 255, 'open|closed', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "open";
        $field->model = $this->model;
        $field->slug = "ping-status";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getCommentStatus() {
     
        $field = new Cell\StringCell();
        $field->name = "comment_status";
        $field->setValidation(0, 255, 'open|closed', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "open";
        $field->model = $this->model;
        $field->slug = "comment-status";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getStatus() {
    
        $field = new Cell\StringCell();
        $field->name = "status";
        $field->setValidation(0, 255, 'publish|inherit|draft', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "publish";
        $field->model = $this->model;
        $field->slug = "status";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getExcerpt() {
    
        $field = new Cell\StringCell();
        $field->name = "excerpt";
        $field->setValidation(0, 500, '', '[<>]+');
        $field->alias = $this->alias;
        $field->default = "";
        $field->model = $this->model;
        $field->slug = "excerpt";
        $this->field_cache[$field->slug] = $field;
        return $field;
    }

    
    function getPublish() {
   
        $field = new Cell\FlagCell();
        $field->name = "publish";
 
        $field->setValidation(0, 1);
        $field->alias = $this->alias;
        $field->default = 0;
        $field->model = $this->model;
        $field->slug = "publish";
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
            case 'date-created':
                return $this->dateCreated();
                break;
            case 'last-updated':
                return $this->lastUpdated();
                break;
            case 'title':
                return $this->getTitle();
                break;
            case 'content':
                return $this->getContent();
                break;
            case 'post-mime-type':
                return $this->getPostMimeType();
                break;
            case 'post-type':
                return $this->getPostType();
                break;
            case 'menu-order':
                return $this->getMenuOrder();
                break;
            case 'guid':
                return $this->getGuid();
                break;
            case 'post-name':
                return $this->getPostName();
                break;
            case 'ping-status':
                return $this->getPingStatus();
                break;
            case 'comment-status':
                return $this->getCommentStatus();
                break;
            case 'status':
                return $this->getStatus();
                break;
            case 'excerpt':
                return $this->getExcerpt();
                break;
            case 'publish':
                return $this->getPublish();
                break;
        }
    }


    function getAllAliases() {
        $arr=[];
        $arr[] = '--id';
        $arr[] = '--parent';
        $arr[] = 'title';
        $arr[] = 'content';
        $arr[] = 'post-mime-type';
        $arr[] = 'post-type';
        $arr[] = 'menu-order';
        $arr[] = 'guid';
        $arr[] = 'post-name';
        $arr[] = 'ping-status';
        $arr[] = 'comment-status';
        $arr[] = 'status';
        $arr[] = 'excerpt';
        $arr[] = 'publish';
        $arr[] = 'date-created';
        $arr[] = 'last-updated';
        return $arr;
    }


    function getSummaryAliases() {
        $arr=[];
        $arr[] = 'title';
        $arr[] = 'post-name';
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