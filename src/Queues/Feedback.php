<?php

namespace GenerCodeOrm\Queues;

class Feedback {

    protected $id = 0;

    function __construct() {
        $this->create();
    }

    function getModel() {
        return app()->makeWith(Model::class, ["name"=>"queue"]);
    }

    function create($name) {
        $profile = app()->get("profile");
        $model = $this->getModel();
    
        $params  = [
            "user-login-id"=>$profile->id,
            "name"=>$name,
            "progress"=>"PENDING"
        ];


        $data = new \GenerCodeOrm\DataSet($model);
        foreach($model->root->cells as $alias=>$cell) {
            if(isset($params[$alias])) {
                $bind = new \GenerCodeOrm\Binds\SimpleBind($cell, $params[$alias]);
                $data->addBind($alias, $bind);
            }
        }

        $data->validate();

        $this->id = $model->setFromEntity(true)->insertGetId($data->toCellNameArr());
    }

    public function update($status) {
        $model = $this->getModel();
        $model->where("id", "=", $this->id);
        return $model->setFromEntity()->update(["progress"=>$status]);
    }

    public function clear() {
        $model = $this->getModel();
        $model->where("id", "=", $this->id);
        return $model->setFromEntity()->delete();
    }

    public function getId() {
        return $this->id;
    }

}