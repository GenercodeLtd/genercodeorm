<?php



class SchemaFactory extends \GenerCodeOrm\Factory {

    
    function __construct($slug = "") {
 
        $this->products["models"] = function() {
            return new PressToJam\Schemas\Models();
        };
 
        $this->products["route-flow"] = function() {
            return new PressToJam\Schemas\RouteFlow();
        };
 
        $this->products["projects"] = function() {
            return new PressToJam\Schemas\Projects();
        };
 
        $this->products["accounts"] = function() {
            return new PressToJam\Schemas\Accounts();
        };
 
        $this->products["fields"] = function() {
            return new PressToJam\Schemas\Fields();
        };
 
        $this->products["sections"] = function() {
            return new PressToJam\Schemas\Sections();
        };
 
        $this->products["profiles"] = function() {
            return new PressToJam\Schemas\Profiles();
        };
 
        $this->products["states"] = function() {
            return new PressToJam\Schemas\States();
        };
 
        $this->products["dictionary-templates"] = function() {
            return new PressToJam\Schemas\DictionaryTemplates();
        };
 
        $this->products["pages"] = function() {
            return new PressToJam\Schemas\Pages();
        };
 
        $this->products["sync-db-log"] = function() {
            return new PressToJam\Schemas\SyncDbLog();
        };
 
        $this->products["user-login"] = function() {
            return new PressToJam\Schemas\UserLogin();
        };
   
    }

   
    
}