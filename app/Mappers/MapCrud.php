<?php
namespace GenerCodeOrm\Mappers;

use Illuminate\Support\Facades\DB;
use GenercodeCore\Facades\Schema;

class MapCrud extends Map
{
  
    public function post(\GenercodeCore\DataSet $data)
    {
        $binds = $data->values;
        foreach($binds as $alias=>$bind) {
            $cols[$bind->cell->name] = $bind->value;
        }
        return $this->query->insertGetId($cols);
    }


    public function delete(\GenercodeCore\DataSet $data)
    {
        $join = new MapJoins($this->query);
        $bind = $data->getBind("--id");
        $this->query->where($bind->cell->name, $bind->value);

        $join->buildDown($collection);

        $buildDown = function($cell) {
            foreach($cell->reference as $child) {
                $child_schema = Schema::get($child);
                $child_parent = $child_schema->get("--parent");

                $this->buildJoin($cell->schema, $cell, $child_schema, $child_parent, false);
                $buildDown($child_schema->get("--id"));
            }
        };

        $buildDown($bind->cell);
        $this->query->delete();
    }


    public function update(\GenercodeCore\DataSet $data)
    {
        $bind = $data->getBind("--id");
        $this->query->where($bind->cell->name, $bind->value);

        $cols = [];
        $binds = $data->values;
        foreach($binds as $alias => $bind) {
            if ($alias == "--id") continue;
            $cols[$bind->cell->name] = $bind->value;
        }

        $this->query->update($cols);
    }


    public function select(\GenercodeCore\DataSet $data)
    {
        $bind = $data->getBind("--id");
        $this->query->where($bind->cell->name, $bind->value);
        return $this->query->get();
    }


    public function resort(array $data)
    {
        if (count($data) == 0) return;
        
        $dataSet = $data->first;
        $sort = $dataSet->getBind("--sort");
        $id = $dataSet->getBind("--id");

        $sql = "UPDATE " . $this->table . " SET " . $sort->cell->name . " = ? WHERE " . $id->cell->name . " = ?";

        $stmt = new MapPrepared($this->query);
        $stmt->prepare($sql);

        foreach($data as $dataSet) {
            $stmt->execute($dataSet->toArr());
        }
    }

    
}
