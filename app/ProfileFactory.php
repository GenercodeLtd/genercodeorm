<?php
namespace GenerCodeOrm;

class ProfileFactory {

    protected $profiles = [];

    
    function __invoke($profile_slug) {
        if (!isset($this->profiles[$profile_slug])) {
            throw new \Exception("Slug doesn't exist in factory: " . $profile_slug);
        }
        
        return ($this->profiles[$profile_slug])();
    }
}