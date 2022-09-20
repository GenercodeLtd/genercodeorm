<?php
namespace GenerCodeOrm;

class SchemaFactory {

    protected $schemas = [];

    
    function __invoke($schema_slug) {
        if (!isset($this->schemas[$schema_slug])) {
            throw new \Exception("Slug doesn't exist in factory: " . $schema_slug);
        }
        
        return ($this->schemas[$schema_slug])();
    }
}