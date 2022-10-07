<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileHandler
{
    protected $disk;

    public function __construct(\Illuminate\Filesystem\Filesystem $disk)
    {
      $this->disk = $disk;
    }



    public function uniqueKey($dir, $ext) {
        $key = "";
        do {
            $key = uniqid() . "." . $ext;
        } while($this->disk->has($dir . $key));
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
            $res = $this->disk->put($dir . $key, file_get_contents($name));
            $params[$alias] = $dir . $key;
        }
        return $params;
    }


    public function patchFile($alias, $cell, $src) {
        
        $dir = "assets/" . $schema->table;
        if (isset($_FILES[$alias])) {
            $cell->validateUpload($file);
            $res = $this->disk->put($dir . $key, file_get_contents($file['tmp_name']));
        }
    }


    public function deleteFiles($data) {
        $schema = $this->repo->getSchema("");
        foreach($schema->cells as $alias=>$cell) {
            if (get_class($cell) == Cells\AssetCell::class) {
                $key = $data->{ $alias };
                $res = $this->disk->delete($key);
            }
        }
    }


    public function get($src) {
        return $this->disk->get($src);
    }   

    public function delete($src) {
        return $this->disk->delete($src);
    } 
    
}
