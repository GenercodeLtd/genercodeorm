<?php

namespace GenerCodeOrm;

use Illuminate\Container\Container;
use \GenerCodeOrm\Exceptions as Exceptions;
use \GenerCodeOrm\DataSet;
use \GenerCodeOrm\InputSet;
use \GenerCodeOrm\Binds as Binds;
use \GenerCodeOrm\Model;
use \GenerCodeOrm\FileHandler;
use \GenerCodeOrm\Cells as Cells;
use \GenerCodeOrm\Entity;
use \GenerCodeOrm\Builder as Builder;

class Repository extends App
{

    use Children;

    protected $to;
    protected $fields = [];
    protected $children = [];
    protected $limit = 0;
    protected $offset = 0;
    protected $order = [];
    protected $filters = [];
    protected $with = [];





    protected function audit($id, $action, ?array $data = null)
    {
        $data = ($data) ? json_encode($data) : "{}";
    
        $model = new \GenerCodeOrm\Models\Audit();
        $model->withAuthen()->create($data);
    }




    private function buildStructure()
    {
        //define the structure
        if ($this->to) {
            $model->to($this->to);
        }

        if ($this->fields) {
            $set = new InputSet($this->name);
            $set->data($this->fields);
            $model->fields($set);
        } else {
            $model->fields();
        }
    }


    private function getWhere(): InputSet
    {
        $where = [];
        $set = new InputSet($this->name);
        foreach ($this->filters as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                $set->addData($key, $val);
            }
        }
        return $set;
    }


    private function setLimit($model, $struc)
    {
        if ($struc->offset) {
            $model->skip($struc->offset);
        }
        if ($struc->limit) {
            $model->take($struc->limit);
        }
    }



    public function get($params, $structure)
    {
        return $this->model->with($structure['with'])->fields()->owner()->get()->toArray();
       // $this->buildStructure();


      /*  if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        
        $where = $this->getWhere();

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $this->setLimit($model);

        if ($model->root->has("--sort")) {
            $orderSet = new InputSet($this->name);
            $orderSet->data(["--sort"=>"ASC"]);
            $model->order($orderSet);
        } else if ($this->order) {
            $orderSet = new InputSet($this->name);
            $orderSet->data($this->order);
            $model->order($orderSet);
        }

        $res = $model->setFromEntity()->get()->toArray();
        if ($this->children) {
            $this->addChildren($this->name, $model, $res);
        }
        return $this->trigger("get", $res);
        */
    }


    public function active($params, $structure)
    {
        return $this->model->with($structure['with'])
            ->fields()
            ->owner()
            ->find($params[$this->model->getPrimaryKey()]);

        return $this->trigger("get", $res);
    }



    public function getFirst()
    {
        $this->order["--id"] = "ASC";
        $this->offset = 0;
        $this->limit = 1;
        return $this->getActive();
    }


    public function getLast()
    {
        $this->order["--id"] = "DESC";
        $this->offset = 0;
        $this->limit = 1;
        return $this->getActive();
    }


    public function count()
    {
        $model= $this->builder();

        if (!$this->profile->allowedAdminPrivilege($this->name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $this->buildStructure($model);

        $where = $this->getWhere();

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $id = $model->root->get("--id");
        $model->select($model->raw("count(" . $id->getDBAlias() . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        return  $model->get()->first();
    }


    public function apply($arr) {
        foreach($arr as $key=>$val) {
            if (substr($key, 0, 2) == "__") {
                $key = substr($key, 2);
                $this->$key = $val;
            } else {
                $this->filters[$key] = $val;
            }
        }
    }

   
}
