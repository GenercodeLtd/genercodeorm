<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileHandler
{
    protected $disk;

    public function __construct(\Illuminate\Filesystem\FilesystemManager $disk, ?string $name)
    {
        $this->disk = $file->disk($name);
    }



    public function uniqueKey($dir, $ext) {
        $key = "";
        do {
            $key = uniqid() . "." . $ext;
        } while($this->disk->has($dir . $key));
        return $key;
    }
    


    public function uploadFile(Binds\AssetBind $bind) {
        $dir = "assets/" . $bind->cell->entity->table . "/";
        $name = $bind->value['tmp_name'];
        $key = $this->uniqueKey($dir, pathinfo($name, \PATHINFO_EXTENSION));
        $res = $this->disk->put($dir . $key, $bind->getBody());
        return $dir . $key;
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
