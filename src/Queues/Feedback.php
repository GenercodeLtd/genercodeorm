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

    function create() {
        $profile = app()->get("profile");
        $model = new \GenerCodeOrm\Model\Queue();
        $model->user_login_id = $profile->id;
        $model->progress = "PENDING";
        $model->save();
        $this->id = $model->id;
    }

    public function update($status) {
        $model = new \GenerCodeOrm\Model\Queue();
        $queue = $model->find($this->id);
        $queue->progress = $status;
        $queue->save();
    }

    public function clear() {
        $model = new \GenerCodeOrm\Model\Queue();
        $queue = $model->find($this->id);
        $queue->delete();
    }

    public function getId() {
        return $this->id;
    }

}