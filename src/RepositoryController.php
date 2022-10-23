<?php

namespace GenerCodeOrm;

use Illuminate\Container\Container;
use Illuminate\Support\Fluent;

class RepositoryController extends AppController
{
    private function findChildLeaves(array $children, Entity $entity = null)
    {
        if (!$entity) {
            $entity = $this->entities[""];
        }
        $id = $entity->get("--id");

        $matches = [];
        foreach ($id->reference as $child) {
            if (in_array($child, $children)) {
                $peek = ($this->profile->factory)($child);
                if (!$peek) {
                    $matches[$child] = $peek;
                    continue;
                }

                $res = $this->findChildLeaves($children, $peek);
                if (!$res) {
                    $matches[$child] = $peek;
                    continue;
                }

                //if we get this far, then we new matches
                $matches = array_merge($matches, $res);
            } else {
                $peek = ($this->profile->factory)($child);
                $res = $this->findChildLeaves($children, $peek);
                $matches = array_merge($matches, $res);
            }
        }

        return $matches;
    }



    private function getRow($rows, $id)
    {
        $filtered = array_filter($rows, function ($row) use ($id) {
            return $row->{"--id"} == $id;
        });

        if (count($filtered) > 0) {
            return array_values($filtered)[0];
        }
    }


    private function tidyChildren($obj)
    {
        foreach ($obj as $key=>$val) {
            if (is_array($val)) {
                $obj->$key = array_values($val);
                foreach ($obj->$key as $cval) {
                    $this->tidyChildren($cval);
                }
            }
        }
    }



    private function addChildren($name, $model, $children, &$rows)
    {
        if (!is_array($children)) {
            $children = [$children];
        }
        $leaves = $this->findChildLeaves($children, $model->root);

        $ids = [];

        foreach ($rows as $row) {
            $ids[] = $row->{"--id"};
        }

        $idCell = $model->root->get("--id");

        foreach ($idCell->reference as $branch) {
            $entity = ($this->profile->factory)($branch);
            $leaves = $this->findChildLeaves($children, $entity);

            if (!$leaves) {
                $child_model = $this->model($branch);
                $child_model->root->slug = $branch;
                $bind = new Binds\SetBind($child_model->root->get("--parent"), $ids);
                $child_model->filter($bind);
                $child_model->fields();

                $results = new Builder\ResultsTree($child_model->entities);

                $cursor = $child_model->setFromEntity()->cursor();

                foreach ($cursor as $result) {
                    $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                    $results->toTree($orig, $result, $child_model->root);
                }
            } else {
                foreach ($leaves as $leaf=>$entity) {
                    $child_model = $this->model($leaf);
                    $child_model->to($branch);
                    $child_model->fields();

                    $bind = new Binds\SetBind($child_model->entities[$branch]->get("--parent"), $ids);
                    $child_model->filter($bind);

                    $results = new Builder\ResultsTree($child_model->entities);
                    $cursor = $child_model->setFromEntity()->cursor();

                    foreach ($cursor as $result) {
                        $orig = $this->getRow($rows, $result->{$branch . "/--parent"});
                        $results->toTree($orig, $result, $child_model->entities[$branch]);
                    }
                }
            }
        }

        foreach($rows as $row) {
            $this->tidyChildren($row);
        }
    }





    private function buildStructure($model, array $arr)
    {
        //define the structure
        if (isset($arr["__to"])) {
            $model->to($arr["__to"]);
        }

        if (isset($arr["__fields"])) {
            $model->fields($arr["__fields"]);
        } else {
            $model->fields();
        }
    }


    private function getWhere($name, array $params): InputSet
    {
        $where = [];
        $set = new InputSet($name);
        foreach ($params as $key=>$val) {
            if (substr($key, 0, 2) != "__") {
                $set->addData($key, $val);
            }
        }
        return $set;
    }


    private function setLimit($model, array $params)
    {
        if (isset($params["__offset"])) {
            $model->skip($params["__offset"]);
        }
        if (isset($params["__limit"])) {
            $model->take($params["__limit"]);
        }
    }



    public function get(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $arr = $params->toArray();

        $this->buildStructure($model, $arr);

        $where = $this->getWhere($name, $params->toArray());

        $dataSet = new DataSet($model);
        $dataSet->data($where);
        $dataSet->validate();

        $model->filterBy($dataSet);

        $this->setLimit($model, $arr);

        if (isset($params["__order"])) {
            $orderSet = new InputSet($name);
            $orderSet->data($params["__order"]);
            $model->order($orderSet);
        }

        $res = $model->setFromEntity()->get()->toArray();
        if (isset($params["__children"])) {
            $this->addChildren($name, $model, $params["__children"], $res);
        }
        return $this->trigger($name, "get", $res);
    }


    public function getActive(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $arr = $params->toArray();
        $this->buildStructure($model, $arr);

        $where = $this->getWhere($name, $arr);
        $model->take(1);

        $res = $model->setFromEntity()->get()->first();
        if ($res === null) {
            $res = new \StdClass();
        } else {
            if (isset($params["__children"])) {
                $this->addChildren($name, $model, $params["__children"], [$res]);
            }
        }
        return $this->trigger($name, "get", $res);
    }



    public function getFirst($name, Fluent $params)
    {
        $params["__order"] = ["--id", "ASC"];
        $params["__offset"] = 0;
        $params["__limit"] = 1;
        return $this->getActive($name, $params);
    }


    public function getLast($name, Fluent $params)
    {
        $params["__order"] = ["--id", "DESC"];
        $params["__offset"] = 0;
        $params["__limit"] = 1;
        return $this->getActive($name, $params);
    }


    public function count(string $name, Fluent $params)
    {
        $this->checkPermission($name, "get");

        $model= $this->model($name);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $arr = $params->toArray();

        $this->buildStructure($model, $arr);

        $where = $this->getWhere($name, $arr);

        $id = $model->root->get("--id");
        $name = ($model->use_alias) ? $model->root->alias . "." . $id->name : $id->name;
        $model->select($model->raw("count(" . $name . ") as 'count'"))
        ->setFromEntity()
        ->take(1);
        return  $model->get()->first();
    }


    public function reference(string $name, string $field, $id, ?Fluent $params)
    {
        $this->checkPermission($name, "get");

        $ref = $this->app->make(Reference::class);

        $model= $this->model($name);
        $ref->setRepo($name, $field, $id, $model);

        if (!$this->profile->allowedAdminPrivilege($name)) {
            $model->secure($this->profile->name, $this->profile->id);
        }

        $this->buildStructure($model, $params->toArray());

        $data = $this->createDataSet($this->where);
        $query->filter($data);

        $fields = [];
        $recursive = null;
        $aggregator = new Cells\AggregatorStringCell();
        $aggregator->alias = "value";
        foreach ($model->root->cells as $cell) {
            if ($cell->summary) {
                $aggregator->addCell($cell);
            } elseif ($cell->alias == "--recursive") {
                $recursive = $cell;
            }
        }
        $idCell = $schema->get("--id");
        $idCell->alias = "key";
        $fields[$idCell->alias]=$idCell;
        $fields[$aggregator->alias] = $aggregator;
        if ($recursive) {
            $recursive->alias = "--recursive";
            $fields[$recursive->alias] = $recursive;
        }

        $raw_sql = $idCell->schema->alias . "." . $idCell->name . " as 'key'";
        $raw_sql .=", CONCAT_WS(' ', " . implode(",", $fields) . ") AS 'value'";
        if ($recursive) {
            $raw_sql .= ", " . $recursive->schema->alias . "." . $recursive->name . " as '--recursive'";
        }

        $query->select($query->raw($raw_sql));
        return $query->get()->toArray();
        return $model->getAsReference();
    }
}