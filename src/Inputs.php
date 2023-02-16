<?php

namespace GenerCodeOrm;

class Inputs {


    protected $errors = [];
    protected $params = [];
    protected $structure = ['with'=>[], 'group'=>[], 'order'=> [], 'fields' => [], 'limit'=>null, 'offset'=> null, 'children'=>[]];
    protected $use_aliases = false;

    function getName($cell) {
        return ($this->use_aliases) ? $cell->dbAlias() : $cell->name;
    }


    function setParent($request, $entity) {
        $cell = $entity->get("--parent");
        if (!$cell) {
            throw new \Exception("Parent cell doesn't exist");
        }

        $name = $this->getName($cell);
        $this->params[$name] = $cell->clean($request->input("--parent"));
        
        $validate = $cell->validate($this->params[$name]);
        if ($validate) {
            $this->errors["--parent"] = [
                "code" => $validate,
                "value" => $request->get("--parent")
            ];
        }
    }


    function setID($request, $entity) {
        $cell = $entity->get("--id");
        if (!$cell) {
            throw new \Exception("ID cell doesn't exist");
        }
        
        $name = $this->getName($cell);
        $this->params[$name] = $cell->clean($request->input("--id"));
        
        $validate = $cell->validate($this->params[$name]);
        if ($validate) {
            $this->errors["--id"] = [
                "code" => $validate,
                "value" => $request->get("--id")
            ];
        }
    }


    function applyAssetCell($alias, $cell, $file) {
        if (!$file->isValid()) {
            $this->errors[$alias] = [
                "code" => 1
            ];
            return;
        }

        $ext = $file->extension();
        $valid = $cell->validateExtension($ext);
        if ($valid) {
            $this->errors[$alias] = [
                "code" => $valid
            ];
            return;
        }

        $this->params[$cell->getName()] = $file;
    }

    function applyCell($request, $alias, $cell) {
        if (get_class($cell) == Cells\AssetCell::class and $request->hasFile($alias)) {
            $this->applyAssetCell($alias, $cell, $request->file($alias));
        } else if (!$cell->system and ($cell->required or $request->has($alias))) {
            $value = $cell->clean($request->$alias);
            $valid = $cell->validate($value);

            if ($valid) {
                $this->errors[$alias] = [
                    "code" => $valid,
                    "value" => $request->$alias
                ];
            }
           
            $this->params[$this->getName()] = $value;
        }
    }


    function applyData($request, $entity) {
        foreach($entity->cells as $alias=>$cell) {
           $this->applyCell($request, $alias, $cell);
        }
    }


    function applyFilters($request, $entity) {
        foreach($entity->cells as $alias=>$cell) {

        }
    }


    function applyStructure($request, $entity) {

        if ($request->has("__to")) {
            $to = $request->to;
            $this->structure["with"] = [];
            $p_entity = $entity->parentEntity();
            while($p_entity) {
                $this->structure["with"][] = $p_entity->category_name;
                if ($p_entity->kebab_name == $to) {
                    break;
                }
                $p_entity = $p_entity->parentEntity();
            }
        }

        if ($request->has("__order")) {
            $this->structure["order"] = [];
            foreach($request->__order as $alias=>$dir) {
                if (isset($entity->cells[$alias])) {
                    $this->structure["order"][$alias] = $dir;
                }
            }
        }


        if ($request->has("__fields")) {

        }

        if ($request->has("__limit")) {
            $this->structure["limit"] = $request->__limit;
        }

        if ($request->has("__offset")) {
            $this->structure["offset"] = $request->__offset;
        }

        if ($request->has("__children")) {

        }


        if ($request->has("__group")) {
            $this->structure["group"] = [];
            foreach($request->__group as $alias) {
                if (isset($entity->cells[$alias])) {
                    $this->structure["group"][] = $entity->cells[$alias]->getDBAlias();
                }
            }
        }
    }

    function isValid() {
        return (count($this->errors) > 0) ? false : true;
    }

    function invalidate() {
        //throw an exception
        throw new \Exception();
    }

    function asArray() {
        return $this->params;
    }


    function structure() {
        return $this->structure;
    }
}