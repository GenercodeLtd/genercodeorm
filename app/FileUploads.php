<?php

namespace GenerCodeOrm;

use Psr\Http\Message\ServerRequestInterface;

class FileUploads
{
    protected $storage;

    public function __construct()
    {
      
    }


    public function uniqueKey($ext) {
        $key = "";
        do {
            $key = uniqid($this->dir) . "." . $ext;
        } while($this->storage->has($key));
        return $key;
    }


    public function processFiles(\DataSet $data) {
        $binds = $data->getBinds();
        foreach($binds as $bind) {
            if ($bind->cell::class == GenerCodeOrm\Cells\AssetCell::class) {
                $bind->cell->validateUpload($bind->value);
                $temp_location = $bind->value['tmpname'];
                $bind->value = $this->unqiueKey(pathinfo($bind->value['name'], \PATHINFO_EXTENSION));
                $this->storage->save($bind->value, file_get_contents($temp_location));
            }
        }
    }

    public function deleteFiles($value) {
        $this->storage->remove($value);
    }



    
}
