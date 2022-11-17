<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileHandler
{
    protected $disk;

    public function __construct(\Illuminate\Filesystem\FilesystemManager $file)
    {
        $this->disk = $file->disk();
    }


    public function getContentType($ext) {
        $content_types=array(
            "css"=>"text/css",
            "gz"=>"application/gzip",
            "gif"=>"image/gif",
            "htm"=>"text/html",
            "html"=>"text/html",
            "ico"=>"image/vnd.microsoft.icon",
            "jpeg"=>"image/jpeg",
            "js"=>"application/javascript",
            "png"=>"image/png",
            "txt"=>"text/plain",
            "json"=>"application/json",
            "xml"=>"application/xml",
            "pdf"=>"application/pdf",
            "odt"=>"application/vnd.oasis.opendocument.text",
            "ttf"=>"font/ttf",
            "woff"=>"font/woff",
            "woff2"=>"font/woff2",
            "eot"=>"application/vnd.ms-fontobject",
            "svg"=>"image/svg+xml");
        return  (isset($content_types[$ext])) ? $content_types[$ext] : "application/octet-stream";
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
        $content_type = $this->getContentType(\pathinfo($src, \PATHINFO_EXTENSION));
        $res = $this->disk->put($src, $body, ["ContentType"=>$content_type]);
        return "SUCCESS";
    }


    public function get($src) {
        return $this->disk->get($src);
    }   

    public function delete($src) {
        return $this->disk->delete($src);
    } 
    
}
