<?php
namespace GenerCodeOrm\Models;

trait Children {

    private function findChildLeaves(array $children, Entity $entity = null)
    {
        $factory =  app()->get("entity_factory");
        if (!$entity) {
            $entity = $this->entities[""];
        }
        $id = $entity->get("--id");

        $matches = [];
        foreach ($id->reference as $child) {
            if (in_array($child, $children)) {
                $peek = ($factory)->create($child);
                if (!$peek) {
                    $matches[$child] = $peek;
                    continue;
                }

                $res = $this->findChildLeaves($children, $peek);
                if (!$res) {
                    $matches[$child] = $peek;
                    continue;
                }

                //if we get this far, then we new matches
                $matches = array_merge($matches, $res);
            } else {
                $peek = ($factory)->create($child);
                $res = $this->findChildLeaves($children, $peek);
                $matches = array_merge($matches, $res);
            }
        }

        return $matches;
    }



    private function getRow($rows, $id)
    {
        $filtered = array_filter($rows, function ($row) use ($id) {
            return $row->{"--id"} == $id;
        });

        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
    }


    private function tidyChildren($obj)
    {
        foreach ($obj as $key=>$val) {
            if (is_array($val)) {
                $obj->$key = array_values($val);
                foreach ($obj->$key as $cval) {
                    $this->tidyChildren($cval);
                }
            }
        }
    }



    private function addChildren($name, $model, &$rows)
    {
        $factory =  app()->get("entity_factory");
        if (!is_array($this->children)) {
            $this->children = [$this->children];
        }
        $leaves = $this->findChildLeaves($this->children, $model->root);

        $ids = [];

        foreach ($rows as $row) {
            $ids[] = $row->{"--id"};
        }

        $idCell = $model->root->get("--id");

        foreach ($idCell->reference as $branch) {
            $entity = ($factory)->create($branch);
            $leaves = $this->findChildLeaves($this->children, $entity);

            if (!$leaves) {
                $child_model = $this->builder($branch);
                $child_model->root->slug = $branch;
                $bind = new Binds\SetBind($child_model->root->get("--parent"), $ids);
                $child_model->filter($bind);
                $child_model->fields();

                if ($child_model->root->has("--sort")) {
                    $orderSet = new InputSet($branch);
                    $orderSet->data(["--sort"=>"ASC"]);
                    $child_model->order($orderSet);
                }

                $results = new Builder\ResultsTree($child_model->entities);

                $cursor = $child_model->setFromEntity()->cursor();

                foreach ($cursor as $result) {
                    $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                    $results->toTree($orig, $result, $child_model->root);
                }
            } else {
                foreach ($leaves as $leaf=>$entity) {
                    $child_model = $this->builder($leaf);
                    $child_model->to($branch);
                    $child_model->fields();

                    if ($child_model->root->has("--sort")) {
                        $orderSet = new InputSet($leaf);
                        $orderSet->data(["--sort"=>"ASC"]);
                        $child_model->order($orderSet);
                    }

                    $bind = new Binds\SetBind($child_model->entities[$branch]->get("--parent"), $ids);
                    $child_model->filter($bind);

                    $results = new Builder\ResultsTree($child_model->entities);
                    $cursor = $child_model->setFromEntity()->cursor();

                    foreach ($cursor as $result) {
                        $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                        $results->toTree($orig, $result, $child_model->entities[$branch]);
                    }
                }
            }
        }

        foreach($rows as $row) {
            $this->tidyChildren($row);
        }
    }


    
}