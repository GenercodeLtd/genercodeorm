<?php

namespace GenerCodeOrm;

class Collection {

    protected $entity;
    protected $rules = [];
    protected $data = [];
    protected $structure = [];
    protected $collections = []; //nested collections

    function __construct() {

    }

    protected function addUniques() {
    }

    protected function addRule($cell, $additional_rules = []) {
        $rules = $cell->asRules();
        $rules = array_merge($rules, $additional_rules);
        if (count($rules) > 0) $this->rules[$cell->alias] = implode("|", $rules);
    }

    protected function validate($request) {
        $this->addUniques();
        app()->get("validator")->validate($request->all(), $this->rules);
    }

    function active($request) {
        $this->addRule($this->entity->get("--id"), ["required"]);
        $this->validate($request);
    }


    function post($request) {
        if ($this->entity->has("--parent")) {
            $this->addRule($this->entity->get("--parent"), ["required"]);
        }

        foreach ($this->entity->cells as $alias=>$cell) {
            if (!$cell->system and ($cell->required or $request->has($alias))) {
                $this->addRule($cell);
            }
        }
        $this->validate($request);
    }


    function put($request) {
        $this->addRule($this->entity->get("--id"), ["required"]);
        foreach ($this->entity->cells as $alias=>$cell) {
            if (!$cell->system and $request->has($alias) and !$cell->immutable) {
                $this->addRule($cell);
            }
        }
        $this->validate($request);
    }


    function destroy($request) {
        $this->addRule($this->entity->get("--id"), ["required"]);
        $this->validate($request);
    }
}