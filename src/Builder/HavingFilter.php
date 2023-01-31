<?php

namespace GenerCodeOrm\Builder;

use GenerCodeOrm\Cells as Cells;
use GenerCodeOrm\Binds as Binds;

trait HavingFilter
{

    public function havingId($alias, $bind)
    {
        $cls = get_class($bind);
        if ($cls == Binds\SetBind::class) {
            //need to set this
            $this->model->havingIn($alias, $bind->value);
        } else {
            $this->model->having($alias, "=", $bind->value);
        }
    }


    public function havingTime($alias, $bind)
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


    public function havingNumber($alias, $bind)
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


    public function havingFlag($alias, $bind)
    {
        $this->model->having($alias, "=", $bind->value);
    }


    public function havingString($alias, $bind)
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


    public function having(\GenerCodeOrm\Binds\Bind $bind)
    {
        $cell = $bind->cell;
        $name = get_class($cell);
        $alias_name = $cell->getDBAlias();
        if ($name == Cells\IdCell::class) {
            $this->havingId($alias_name, $bind);
        } elseif ($name == Cells\TimeCell::class) {
            $this->havingTime($alias_name, $bind);
        } elseif ($name == Cells\NumberCell::class) {
            $this->havingNumber($alias_name, $bind);
        } elseif ($name == Cells\FlagCell::class) {
            $this->havingFlag($alias_name, $bind);
        } elseif ($name == Cells\StringCell::class) {
            $this->havingString($alias_name, $bind);
        } else {
            throw new \GenerCodeOrm\Exceptions\CellTypeException($name);
        }
    }
}