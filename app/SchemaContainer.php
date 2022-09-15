<?php
namespace PressToJamCore;

class SchemaContainer {

    protected $containers = [];

    function reg(String $slug, Schema $collection) {
        $this->containers[$slug] = $collection;
        return $collection;
    }

    function get(String $slug) {
        if (!isset($this->containers[$slug])) {
            throw new \Exception($slug . " does not exist in container");
        }
        return $this->containers[$slug];
    }

    function has(String $slug) {
        return isset($this->containers[$slug]);
    }

    function getAll() {
        return $this->containers;
    }
}