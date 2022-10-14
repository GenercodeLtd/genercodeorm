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


    private function getAllFields($with_archive = false) : DataView
    {
        $view = new DataView();
        $schemas = $this->repo_schema->getSchemas();
        foreach ($schemas as $slug=>$schema) {
            foreach ($schema->cells as $cell) {
                if (!$with_archive AND $cell->alias == "--archive") continue;
                $view->addCell($slug, $cell);
            }
        }
        return $view;
    }


    public function expandFields() : DataView
    {
        $view = new DataView();
        foreach ($this->fields as $field) {
            if (is_array($field)) {
                if (isset($field["type"])) {
                    if ($field["type"] == "aggregate") {
                        $arr = [];
                        foreach($field["fields"] as $ifield) {
                            $arr[$ifield] = $this->repo_schema->get($ifield);
                        } 
                        $cell = new AggregatorStringCell($arr);
                        if (isset($field["ws"])) $cell->ws = $field["ws"];
                        $cell->alias = $field["name"];
                        $view->addCell($slug, $cell);

                    }   
                } else if (isset($field["path"])) {
                    $slug = $this->repo_schema->getSlug($fields["path"]);
                    foreach($field["fields"] as $field) {
                        $cell = $this->repo_schema->get($field, $slug);
                        $view->addCell($slug, $cell);
                    }
                }
            } else if (strpos($field, "*summary") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                if (!isset($fields[$slug])) $fields[$slug] = [];
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cell) {
                    if ($cell->summary) {
                        $view->addCell($slug, $cell);
                    }
                }
            } elseif (strpos($field, "*") !== false) {
                $slug = trim(str_replace("/*summary", "", $field), "/");
                if (!isset($fields[$slug])) $fields[$slug] = [];
                $schema = $this->repo_schema->getSchema($slug);
                foreach ($schema->cells as $cfield) {
                    $view->addCell($slug, $cell);
                }
            } else {
                $parts = $this->repo_schema->splitNames($field);
                $view->addCell($parts[0], $this->repo_schema->get($parts[1], $parts[0]));
            }
        }
        return $view;
    }




    public function convertFieldsToDataMap(DataView $view) {
        $data = new DataSet();
        $data->bindFromView($view);
        return $data->toCells();
    }

    
    public function getQuery()
    {
        if ($this->to) $this->repo_schema->loadTo($this->to);
        if ($this->children) $this->repo_schema->loadChildren($this->children);
        $fields = (!$this->fields) ? $this->getAllFields() : $this->expandFields();

        $schema = $this->repo_schema->getSchema("");
        $query = $this->buildQuery($schema->table, $schema->alias);
        $query->joinTo($this->repo_schema);
        $query->children($this->repo_schema);


        $data = $this->convertFieldsToDataMap($fields);
        $query->fields($this->repo_schema, $data);

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
                    $query->orderBy($cell->schema->alias . "." . $cell->name, $dir);
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
        $recursive = null;
        foreach($schema->cells as $cell) {
            if ($cell->summary) $fields[] = $cell->schema->alias . "." . $cell->name;
            else if ($cell->alias == "--recursive") $recursive = $cell;
        }
        $idCell = $schema->get("--id");
        $raw_sql = $idCell->schema->alias . "." . $idCell->name . " as 'key'";
        $raw_sql .=", CONCAT_WS(' ', " . implode(",", $fields) . ") AS 'value'";
        if ($recursive) {
            $raw_sql .= ", " . $recursive->schema->alias . "." . $recursive->name . " as '--recursive'";
        }

        $query->select($query->raw($raw_sql));
        return $query->get()->toArray();
    }
}
