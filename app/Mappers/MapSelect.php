<?php
namespace GenerCodeOrm\Mappers;
use Illuminate\Database\Capsule\Manager as Capsule;

class MappedName {
    public $collections = [];
    public $name = "";
    public $field = null;
}

class DataMapper {

    private $query;
    private $collection;

    function __construct() {
    }



    private function buildSelectCols() {
        $cols = [];
        foreach($this->collections as $slug=>$collection) {
            $cells = $collection->getActiveCells();
            foreach($cells as $alias=>$cell) {
                $cols[] = $collection->table . "." . $cell->name . " AS '" . $slug . $alias . "'";
            }
        }
        $this->query->select($cols);
    }


    

    private function build() {
        $this->flattenCollections();
        $this->buildSelectCols();
        $this->buildJoins();
        $this->buildFilter();
    }


    public function map(MetaCollection $collection) {
        $this->collection = $collection;
        $this->query = Capsule::table($this->collection->table);
        $this->build();
    }
   

    public function setOrder(array $orders) {
        $parse_col = $this->parseName($col);
        $col = $this->getCol($parse_col);
        if (!$col) {
            throw "Column doesn't exist error";
        }
        $this->query->orderBy($col->name, $dir);
    }


    public function setLimit($limit, $offset = 0) {
        $this->query->skip($offset)->take($limit);
    }


    public function setGroup(String $group) {
        $parse_col = $this->parseName($col);
        $col = $this->getCol($parse_col);
        if (!$col) {
            throw "Column doesn't exist error";
        }
        $this->query->groupBy($group);
    }



    public function getSQL() {
        return $this->query->getSQL();
    }

    public function prepare() {
        return DB::getPDO()->prepare($this->query->getSQL());
    }


    public function getQuery() {
        return $this->query;
    }
}