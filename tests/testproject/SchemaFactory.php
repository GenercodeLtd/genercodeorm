<?php



class SchemaFactory extends \GenerCodeOrm\SchemaFactory {

    
    function __construct($slug = "") {
 
        $this->schemas["models"] = function() {
            return new PressToJam\Schemas\Models();
        };
 
        $this->schemas["route-flow"] = function() {
            return new PressToJam\Schemas\RouteFlow();
        };
 
        $this->schemas["projects"] = function() {
            return new PressToJam\Schemas\Projects();
        };
 
        $this->schemas["accounts"] = function() {
            return new PressToJam\Schemas\Accounts();
        };
 
        $this->schemas["fields"] = function() {
            return new PressToJam\Schemas\Fields();
        };
 
        $this->schemas["sections"] = function() {
            return new PressToJam\Schemas\Sections();
        };
 
        $this->schemas["profiles"] = function() {
            return new PressToJam\Schemas\Profiles();
        };
 
        $this->schemas["states"] = function() {
            return new PressToJam\Schemas\States();
        };
 
        $this->schemas["dictionary-templates"] = function() {
            return new PressToJam\Schemas\DictionaryTemplates();
        };
 
        $this->schemas["pages"] = function() {
            return new PressToJam\Schemas\Pages();
        };
 
        $this->schemas["sync-db-log"] = function() {
            return new PressToJam\Schemas\SyncDbLog();
        };
 
        $this->schemas["user-login"] = function() {
            return new PressToJam\Schemas\UserLogin();
        };
   
    }

   
    
}