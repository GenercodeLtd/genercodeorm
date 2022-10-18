<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileHandler
{
    protected $disk;

    public function __construct(\Illuminate\Filesystem\AwsS3V3Adapter $disk)
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


    public function put($src, $body) {
        $res = $this->disk->put($src, $body);
        return "SUCCESS";
    }


    public function get($src) {
        return $this->disk->get($src);
    }   

    public function delete($src) {
        return $this->disk->delete($src);
    } 
    
}
