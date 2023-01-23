<?php

namespace GenerCodeOrm\Http\Controllers;

class DataController extends AppController {


    protected function validateChild(string $name, ?Binds\Bind $parent = null) {
        $model= $this->model($name);
        $model->select($model->raw("count(" . $name . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        if ($parent) {
            $model->filter($parent);
        }
        return  $model->get()->first()->count;
    }  


    protected function getEntity($name) {
        return app()->get("factory")->create($name);
    }


    public function validate(string $name = null, int $parent_id = 0)
    {
        $this->checkPermission($name, "get");

        $children = [];
        $bind = null;
        if ($name) {
            $entity = $this->getEntity($name);
            $children = $entity->get("--id")->reference;
            $bind = new Binds\Bind($entity->get("--parent"));
            $bind->value = $parent_id;
        } else {
            $children = app()->get("factory")::getRootEntities();
        }

        $checks = [];
        foreach($children as $slug) {
            $child_entity = $this->getEntity($slug);
            if ($child_entity->min_rows OR $child_entity->max_rows) {

                $checks[$slug] = $this->validateChild($slug, $bind);
            } 
        }

        return $checks;
    }
}