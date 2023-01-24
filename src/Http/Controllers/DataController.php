<?php

namespace GenerCodeOrm\Http\Controllers;
use \GenerCodeOrm\Binds;

class DataController extends AppController {


    protected function validateChild(string $name, ?Binds\SimpleBind $bind = null) {
        $model= $this->model($name);

        $id = $model->root->get("--id");
    
        $model->select($model->raw("count(" . $id->getDBAlias() . ") as 'count'"))
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
        if ($name) {
            $entity = $this->getEntity($name);
            $children = $entity->get("--id")->reference;
        } else {
            $children = app()->get("entity_factory")::getRootEntities();
        }

        $checks = [];
        foreach($children as $slug) {
            try {
                $this->checkPermission($slug, "get");
            } catch(\Exception $e) {
                continue; //just continue if no permission
            }
            
            $child_entity = $this->getEntity($slug);
            $child_entity->alias = "t1";

            if ($child_entity->min_rows OR $child_entity->max_rows) {
                $bind = null;
                if ($child_entity->has("--owner")) {
                    $bind = new Binds\SimpleBind($child_entity->get("--owner"), $this->profile->id);
                } else if ($child_entity->has("--parent")) {
                    $bind = new Binds\SimpleBind($child_entity->get("--parent"), $parent_id);
                }
                $checks[$slug] = $this->validateChild($slug, $bind);
            } 
        }

        return $checks;
    }
}