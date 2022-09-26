<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container as Container;

class Repository extends Model
{
   
    protected $to;
    protected ?array $children = null;
    protected $limit;
    protected $offset;
    protected ?array $order = null;
    protected string $group = "";


    private function getAllFields()
    {
        $schemas = $this->repo_schema->getSchemas();
        $fields = [];
        foreach ($schemas as $slug=>$schema) {
            $fields[$slug] = [];
            foreach ($schema->cells as $cell) {
                if (!$cell->background) {
                    $fields[$slug][] = $cell->alias;
                }
            }
        }
        return $fields;
    }


    private function expandFields()
    {
        $fields = [];
        foreach ($this->fields as $field) {
            if (strpos($field, "*summary") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                if (!isset($fields[$slug])) $fields[$slug] = [];
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cell) {
                    if ($cell->summary) {
                        $fields[$slug][] = $cfield->alias;
                    }
                }
            } elseif (strpos($field, "*") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                if (!isset($fields[$slug])) $fields[$slug] = [];
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cfield) {
                    if ($cfield::class == Cell\TimeCell::class ) {
                        $fields[$slug] = $cfield->alias;
                    } else {
    $fields[$slug][] = $cfield->alias;
}
                }
            } else {
                $parts = $this->repo_schema->splitNames($field);
                if (!isset($fields[$parts[0]])) $fields[$parts[0]] = [];
                $fields[$parts[0]][] = $parts[1];
            }
        }
        return $fields;
    }




    public function convertFieldsToDataMap($fields) {
        $data = new DataSet();
        foreach($fields as $slug=>$fields) {
            $slug_alias = (!$slug) ? "" : $slug . "/";
            foreach($fields as $alias) {
                $data->bind($slug_alias . $alias, $this->repo_schema->get($alias, $slug));
            }
        }
        return $data->toCells();
    }

    
    public function getQuery()
    {
        if ($this->to) $this->repo_schema->loadTo($this->to);
        if ($this->children) $this->repo_schema->loadChildren($this->children);
        $fields = (!$this->fields) ? $this->getAllFields() : $this->expandFields();

        $this->repo_schema->loadReferences($fields);
        

        $schema = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($schema->table, $schema->alias);
        $query->joinTo($this->repo_schema);
        $query->children($this->repo_schema);
        $query->fields($this->repo_schema, $this->convertFieldsToDataMap($fields));

        if ($this->secure) $this->secureQuery($query, $this->to);
       
        $data = $this->createDataSet($this->where);
        $query->filter($data);
        return $query;
    }



    public function get()
    {
        $query = $this->getQuery();
        return $query->get()->first();
    }


    public function getAll()
    {
        $query = $this->getQuery();
        $orders = [];
        if ($this->repo_schema->has("--sort")) {
            $cell = $this->repo_schema->get("--sort");
            $query->orderBy($cell->schema->alias . "." . $cell->name, "ASC");
        } else {
            if ($this->order) {
                foreach ($this->order as $alias=>$dir) {
                    $cell = $this->repo_schema->get($alias);
                    $query->orderBy($cell->schema->alias . "." . $cell->name, "ASC");
                }
            }
        }

   
        if ($this->group) {
            $cell = $this->repo_schema->get($this->group);
            $query->groupBy($cell->schema->table . "." . $cell->name);
        }

        if ($this->limit) $query->take($this->limit);
        if ($this->offset) $query->skip($this->offset);

        return $query->get()->toArray();
    }

    
    public function count()
    {
        if ($this->to) $this->repo_schema->loadTo($this->to);
        $schema = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($schema->table, $schema->alias);
        $query->joinTo($this->repo_schema);
       
        if ($this->secure) $this->secureQuery($query, $this->to);
       
        $id = $schema->get("--id");

        $data = $this->createDataSet($this->where);
        $query->filter($data);
        $query->select($query->raw("count(" . $schema->alias . "." . $id->name . ") as 'count'"))
        ->take(1);

        return $query->get()->first();
    }


    public function getAsReference() {
        if ($this->to) $this->repo_schema->loadTo($this->to);

        $schema = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($schema->table, $schema->alias);
        $query->joinTo($this->repo_schema);

        if ($this->secure) $this->secureQuery($query, $this->to);

        $data = $this->createDataSet($this->where);
        $query->filter($data);

        $fields = [];
        foreach($schema->cells as $cell) {
            if ($cell->summary) $fields[] = $cell->schema->alias . "." . $cell->name;
        }
        $idCell = $schema->get("--id");
        $query->select($query->raw($idCell->schema->alias . "." . $idCell->name . " as 'key', CONCAT_WS(' ', " . implode(",", $fields) . ") AS 'value'"));
        return $query->get()->toArray();
    }
}
