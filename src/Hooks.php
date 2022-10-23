<?php

namespace GenerCodeOrm;
use \Illuminate\Container\Container;

class Hooks extends Factory {

    protected Container $container;

    function __construct(Container $container) {
        $this->container = $container;
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
            return ($this->products[$action])($this->container, $method, $data);
		} else {
            return $data;
        }
	}

}