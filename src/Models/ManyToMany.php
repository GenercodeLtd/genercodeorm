<?php
namespace GenerCodeOrm\Models;

trait ManyToMany {

    public function load($cell, &$rows, $params)
    {
        $builder = new Builder();
        //run builder here
        $repo->apply($params);
        //join the relationship field
        //set the wheres

        $results = $repo->get();
        foreach($results as $irow) {
            //join to parent rows
        }
    }



    public function update($cell, $row_id, $ids)
    {
        $builder = new Builder();
        //get where matches current id

        $res = $builder->from()->where($cell->alias, "=", $row_id)->get()->asArray();
        $compare_ids = [];
        foreach($res as $row) {
            $compare_ids[] = $cell->reference;
        }

        foreach($compare_ids as $id) {
            if (!in_array($id, $ids)) {
                $builder->from()->delete(["id"]);
            }
        }

        foreach($ids as $id) {
            if (!in_array($id, $compare_ids)) {
                $builder->from()->insert([$row_id, $id]);
            }
        }
        
    }


    public function delete($cell, $row_id, $ids = null)
    {
        
    }
    
}