<?php
namespace GenerCodeOrm\Mappers;

use \GenerCodeOrm\Model;


class MapPrepared {

    protected $stmt;
    protected $pdo;
    

    function __construct($query) {
        $this->pdo = $query->connection()->getPdo();
    }



    function prepare($sql)
    {
        try {
            $this->stmt = $this->pdo->prepare($sql);
        } catch(\PDOException $e) {
            throw new Exceptions\SQLException($this->sql, $args, $e->getMessage());
        }
	}

 
    function execute(Model $model) {
        try {
            $this->stmt->execute($model->toArgs());
        } catch(\PDOException $e) {
            throw new Exceptions\SQLException($this->sql, $args, $e->getMessage());
        }
        return $this->stmt;
    }


    function lastID() {
        return $this->pdo->lastInsertId();
    }

}