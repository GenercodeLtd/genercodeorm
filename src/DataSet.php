<?php

namespace GenerCodeOrm;

class DataSet
{
    protected $model;
    protected $binds = [];

    public function __construct(\GenerCodeOrm\Builder\Builder $model)
    {
        $this->model = $model;
    }

    public function __get($key)
    {
        if (isset($this->binds[$key])) {
            return $this->binds[$key]->value;
        } else {
            throw new \Exception($key . " is not set");
        }
    }

    public function __set($key, $val)
    {
        if (!isset($this->binds[$key])) {
            throw new \Exception("Have to bind " . $key . " first");
        }
        $this->binds[$key]->setValue($val);
    }


    public function merge(DataSet $ndata)
    {
        $this->binds = array_merge($this->binds, $ndata->getBinds());
    }


    public function addBind($alias, $bind)
    {
        $this->binds[$alias] = $bind;
    }

    public function data(InputSet $params)
    {
        $data = $params->getData();

        foreach ($data as $slug=>$input) {
            foreach ($input->values as $key=>$val) {
                $cell = $this->model->getCell($key, $slug);
                if (is_array($val)) {
                    if (isset($val['min']) or isset($val["max"])) {
                        $bind = new Binds\RangeBind($cell, $val);
                    } else {
                        $bind = new Binds\SetBind($cell, $val);
                    }
                } else {
                    $bind = new Binds\SimpleBind($cell, $val);
                }
                $this->binds[$cell->alias] = $bind;
            }
        }
    }


    public function getBind($alias)
    {
        if (!isset($this->binds[$alias])) {
            throw new \Exception($alias . " bind doesn't exist");
        }
        return $this->binds[$alias];
    }


    public function apply($params)
    {
        foreach ($params as $key=>$val) {
            $this->$key = $val;
        }
    }


    public function getBinds()
    {
        return $this->binds;
    }


    public function toArr()
    {
        $arr = [];
        foreach ($this->binds as $key=>$val) {
            $arr[$key] = $val->value;
        }
        return $arr;
    }


    public function toCellNameArr($alias = "")
    {
        $arr = [];
        foreach ($this->binds as $key=>$val) {
            $arr[$alias . $val->cell->name] = (get_class($val->cell) == \GenerCodeOrm\Cells\JsonCell::class) ? json_encode($val->value) : $val->value;
        }
        return $arr;
    }


    public function toCells()
    {
        $arr = [];
        foreach ($this->binds as $key=>$val) {
            $arr[$key] = $val->cell;
        }
        return $arr;
    }


    public function validate()
    {
        $errors = [];
        foreach ($this->binds as $slug => $bind) {
            try {
                $bind->validate();
            } catch(Exceptions\ValidationException $e) {
                $errors[$slug] = [$e->getError(), $e->getValue()];
            }
        }

        if (count($errors) > 0) {
            throw new Exceptions\ValidationGroupException($errors);
        }
    }
}
