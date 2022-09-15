<?php

namespace PressToJamCore\Cells;

class AssetCell extends MetaCell {

    protected $token;
    protected $size = 0;
    protected $chunk_size = 0;
    protected $tmp_file_dir;
    protected $hook;
    protected $dir = "";
  

    function __set($name, $value) {
        if (property_exists($this, $name)) $this->$name = $value;
    }

    function __get($name) {
        if ($name == "param_type") return \PDO::PARAM_STR;
        else if (property_exists($this, $name)) return $this->$name;
        else return null;
    }
    
 
    

   
    function tempLocation() {
        return tempnam($this->tmp_file_dir);
    }

   
    function map($val) {
        if (is_array($val)) {
            if (!isset($val['name'])) {
                $val['name'] = $this->uniqueKey($val['ext']);
            }
        } 
        
        $size = (isset($val['size'])) ? $val['size'] : 0;
        $this->validateSize($size);
        if ($this->last_error = ValidationRules::OK) {
            $ext = \pathinfo($val['name'], \PATHINFO_EXTENSION);
            $this->validateValue($ext);
        }

        if ($this->last_error == ValidationRules::OK) {
            return $val['name'];
        } else {
            return null;
        }
    }
 

    public function writeFile($key, $data) {
        $ext = \pathinfo($key, \PATHINFO_EXTENSION);
        if (!$ext) {
            throw new \Exception("No extension for file");
        }
        $writer = \PressToJamCore\WrapperFactory::createS3();
        if (!is_string($data)) {
            $data =  pack('C*', ...$data);
        }
        $writer->push($key, $data);
    } 


    public function writeChunk($chunk, $data) {
        $chunk_name = $this->tempLocation();
        $temp_fp = fopen($chunk_name, "w");
        if (!is_string($data)) {
            $data =  pack('C*', ...$data);
        }
        fwrite($temp_fp, $data);
        return basename($chunk_name);
    }

    public function completeMultipartFileUpload($key, $chunks) {
        $big_file = $this->tempLocation();
        $temp_fp = fopen($big_file, "a");
        foreach($chunks as $chunk) {
            fwrite($temp_fp, file_get_contents($this->tmp_file_dir . "/" . $chunk));
            unlink($this->tmp_file_dir);
        }

        $writer = \PressToJamCore\WrapperFactory::createS3();
        $writer->push($key, file_get_contents($big_file));
        unlink($big_file);
    }


    public function removeAsset($key) {
        $writer = \PressToJamCore\WrapperFactory::createS3();
        $writer->remove($key);
    }


    public function copyAsset($key, $old_file) {
        $writer = \PressToJamCore\WrapperFactory::createS3();
        $writer->copy($key, $old_file);
    }


    public function uniqueKey($ext) {
        $writer = \PressToJamCore\WrapperFactory::createS3();
        $key = "";
        do {
            $key = uniqid($this->dir) . "." . $ext;
        } while($writer->fileExists($key));
        return $key;
    }

    public function view($key) {
        //can set header from extension
        $writer = \PressToJamCore\WrapperFactory::createS3();
        return $writer->get($key);
    }

    function reserve($key) {
        $writer = \PressToJamCore\WrapperFactory::createS3();
        if (!$writer->fileExists($key)) {
            $this->writeFile($key, "");
        }
    }

    function runHook() {
        if ($this->hook) {
            $this->hook();
        }
    }

    function mapToStmtFilter($name) {
        return $name . " = ?";
    }


    function toSchema() {
        $arr = parent::toSchema();
        $arr["type"] = "asset";
        return $arr;
    }

}