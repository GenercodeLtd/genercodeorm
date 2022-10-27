<?php

namespace GenerCodeOrm;

use \Illuminate\Container\Container;


class Migration
{
    protected $app;
    protected $sqls = [];
    protected $errors = [];
    protected $not_rans = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }


    public function addSQL($sql)
    {
        $this->sqls[] = $sql;
    }

    public function run()
    {
        $conn = $this->app->get(\Illuminate\Database\Connection::class);
        $pdo = $conn->getPdo();

        $success = [];
        $errs = [];
        foreach ($this->sqls as $sql) {
            try {
                $pdo->query($sql);
            } catch(\Exception $e) {
                $this->errors[] = $e->getMessage();
                $this->not_rans[] = $sql;
            }
        }
        return (count($this->errors) == 0) ? true : false;
    }

    public function getSQLs() {
        return $this->sqls;
    }


    public function getErrors()
    {
        return $this->errors;
    }


    public function getNotRans()
    {
        return $this->not_rans;
    }
}
