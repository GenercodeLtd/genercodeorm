<?php

namespace GenerCodeOrm\Http\Controllers;
use \GenerCodeOrm\Binds;

class DataController extends AppController {


    protected function validateChild(string $name, ?Binds\SimpleBind $bind = null) {
        $model= $this->model($name);
        $model->select($model->raw("count(" . $name . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        if ($bind) {
            $model->filter($bind);
        } 
        return  $model->get()->first()->count;
    }  


    protected function getEntity($name) {
        return app()->get("entity_factory")->create($name);
    }


    public function validate(string $name = null, int $parent_id = 0)
    {
        $children = [];
        $bind = null;
        if ($name) {
            $this->checkPermission($name, "get");
            $entity = $this->getEntity($name);
            $children = $entity->get("--id")->reference;
            $bind = new Binds\SimpleBind($entity->get("--parent"), $parent_id);
        } else {
            $children = app()->get("entity_factory")::getRootEntities();
        }

        $checks = [];
        foreach($children as $slug) {
            $child_entity = $this->getEntity($slug);
            if ($child_entity->min_rows OR $child_entity->max_rows) {
                $cbind = null;
                if ($child_entity->has("--owner")) {
                    $cbind = new Binds\SimpleBind($child_entity->get("--owner"), $this->profile->id);
                }

                $ibind = ($cbind) ? $cbind : $bind;
                $checks[$slug] = $this->validateChild($slug, $ibind);
            } 
        }

        return $checks;
    }
}