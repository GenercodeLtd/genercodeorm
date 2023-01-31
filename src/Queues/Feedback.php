<?php

namespace GenerCodeOrm\Queues;
use \GenerCodeOrm\Builder\Builder;

class Feedback {

    protected $name = 0;
    protected $id;

    function __construct($id = null) {
        if (!$id) $this->create();
        else $this->id = $id;
    }

    function getModel() {
        return app()->makeWith(Builder::class, ["name"=>"queue"]);
    }

    function create() {
        $profile = app()->get("profile");
        $model = $this->getModel();
    
        $params  = [
            "user-login-id"=>$profile->id,
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
        return $model->setFromEntity(true)->update(["progress"=>$status]);
    }

    public function clear() {
        $model = $this->getModel();
        $model->where("id", "=", $this->id);
        return $model->setFromEntity(true)->delete();
    }

    public function getId() {
        return $this->id;
    }

}