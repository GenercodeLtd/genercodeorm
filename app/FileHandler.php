<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileHandler
{
    protected $file;
    protected $prefix;

    public function __construct(\Illuminate\Filesystem\FilesystemManager $file, $prefix)
    {
      $this->file = $file;
      $this->prefix = $this->parsePrefix($prefix);
    }



    protected function parsePrefix($prefix) {
        $prefix = trim($prefix, "/");
        if ($prefix) $prefix .= "/";
        return $prefix;
    }


    public function uniqueKey($dir, $ext) {
        $key = "";
        do {
            $key = uniqid() . "." . $ext;
        } while($this->file->disk("s3")->has($this->prefix . $dir . $key));
        return $key;
    }
    


    public function uploadFiles($cells) {
        $params = [];
        foreach($cells as $alias=>$cell) {
            $dir = "assets/" . $cell->schema->table;
            if (!isset($_FILES[$alias])) continue;
            $file = $_FILES[$alias];
            $cell->validateUpload($file);
            $name = $file['tmp_name'];
            $key = $this->uniqueKey($dir, pathinfo($name, \PATHINFO_EXTENSION));
            $res = $this->file->disk("s3")->put($this->prefix . $dir . $key, file_get_contents($name));
            $params[$alias] = $dir . $key;
        }
        return $params;
    }


    public function patchFile($alias, $cell, $src) {
        
        $dir = "assets/" . $schema->table;
        if (isset($_FILES[$alias])) {
            $cell->validateUpload($file);
            $res = $this->file->disk("s3")->put($this->prefix . $dir . $key, file_get_contents($file['tmp_name']));
        }
    }


    public function deleteFiles($data) {
        $schema = $this->repo->getSchema("");
        foreach($schema->cells as $alias=>$cell) {
            if (get_class($cell) == Cells\AssetCell::class) {
                $key = $data->{ $alias };
                $res = $this->file->disk("s3")->delete($this->prefix . $key);
            }
        }
    }


    public function get($src) {
        return $this->file->disk("s3")->get($this->prefix . $src);
    }   

    public function delete($src) {
        return $this->file->disk("s3")->delete($this->prefix . $src);
    } 
    
}
