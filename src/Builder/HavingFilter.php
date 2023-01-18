<?php

namespace GenerCodeOrm\Builder;

use GenerCodeOrm\Cells as Cells;
use GenerCodeOrm\Binds as Binds;

class HavingFilter
{
    protected \GenerCodeOrm\Model $model;

    public function __construct(\GenerCodeOrm\Model $model)
    {
        $this->model = $model;
    }


    public function filterId($alias, $bind)
    {
        $cls = get_class($bind);
        if ($cls == Binds\SetBind::class) {
            //need to set this
            $this->model->havingIn($alias, $bind->value);
        } else {
            $this->model->having($alias, "=", $bind->value);
        }
    }


    public function filterTime($alias, $bind)
    {
        $cls = get_class($bind);
        if ($cls == Binds\RangeBind::class) {
            if (isset($bind->value["min"])) {
                $this->model->having($alias, ">=", $bind->value["min"]);
            }

            if (isset($bind->value["max"])) {
                $this->model->having($alias, "<=", $bind->value["max"]);
            }
        } elseif ($cls == Binds\SetBind::class) {
            $this->model->havingIn($alias, $bind->value);
        } else {
            $this->model->having($alias, "=", $bind->value);
        }
    }


    public function filterNumber($alias, $bind)
    {
        $cls = get_class($bind);
        if ($cls == Binds\RangeBind::class) {
            if (isset($bind->value["min"])) {
                $this->model->having($alias, ">=", $bind->value["min"]);
            }

            if (isset($bind->value["max"])) {
                $this->model->having($alias, "<=", $bind->value["max"]);
            }
        } elseif ($cls == Binds\SetBind::class) {
            $this->model->havingIn($alias, $bind->value);
        } else {
            $this->model->having($alias, "=", $bind->value);
        }
    }


    public function filterFlag($alias, $bind)
    {
        $this->model->having($alias, "=", $bind->value);
    }


    public function filterString($alias, $bind)
    {
        $cls = get_class($bind);
        if ($cls == Binds\SetBind::class) {
            $values = $bind->value;
            $this->having(function ($query) use ($cell, $values) {
                $first=array_shift($bind->value);
                $query->having($alias, "like", $first);
                foreach ($values as $val) {
                    $query->orHaving($alias, "like", $val);
                }
            });
        } else {
            $this->model->having($alias, "=", $bind->value);
        }
    }


    public function filter(\GenerCodeOrm\Binds\Bind $bind)
    {
        $cell = $bind->cell;
        $name = get_class($cell);
        $alias_name = $cell->getDBAlias();
        if ($name == Cells\IdCell::class) {
            $this->filterId($alias_name, $bind);
        } elseif ($name == Cells\TimeCell::class) {
            $this->filterTime($alias_name, $bind);
        } elseif ($name == Cells\NumberCell::class) {
            $this->filterNumber($alias_name, $bind);
        } elseif ($name == Cells\FlagCell::class) {
            $this->filterFlag($alias_name, $bind);
        } elseif ($name == Cells\StringCell::class) {
            $this->filterString($alias_name, $bind);
        } else {
            throw new \GenerCodeOrm\Exceptions\CellTypeException($name);
        }
    }
}