<?php

namespace GenerCodeOrm;
use \Illuminate\Container\Container;

class Hooks extends Factory {

    
    function __construct() {
        $config = app()->get("config");
        if ($config->has("hooks")) $this->loadHooks($config->get("hooks"));
    }

    function loadHooks(array $arr) {
        foreach ($arr as $name=>$block) {
            foreach ($block as $method=>$func) {
                $this->products[$name . "." . strtolower($method)] = $func;
            }
        }
    }

    
	function trigger($name, $method, $data)
	{
        $action = $name . "." . strtolower($method);
		if (isset($this->products[$action])) {
            return ($this->products[$action])($method, $data);
		} else {
            return $data;
        }
	}

}