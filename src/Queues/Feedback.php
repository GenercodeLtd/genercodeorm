<?php

namespace GenerCodeOrm\Queues;
use \GenerCodeOrm\Model;

class Feedback {

    protected $id = 0;

    function __construct($id) {
        $this->id = $id;
        $this->create();
    }

    function getModel() {
        return app()->makeWith(Model::class, ["name"=>"queue"]);
    }

    function create() {
        $profile = app()->get("profile");
        $model = $this->getModel();
    
        $params  = [
            "user-login-id"=>$profile->id,
            "name" => $this->id,
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

        $model->setFromEntity(true)->insertGetId($data->toCellNameArr());
    }

    public function update($status) {
        $model = $this->getModel();
        $model->where("name", "=", $this->id);
        return $model->setFromEntity()->update(["progress"=>$status]);
    }

    public function clear() {
        $model = $this->getModel();
        $model->where("name", "=", $this->id);
        return $model->setFromEntity()->delete();
    }

    public function getId() {
        return $this->id;
    }

}