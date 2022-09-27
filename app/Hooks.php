<?php

namespace GenerCodeOrm;

class Hooks extends Factory {

    function __construct($arr) {
        foreach ($arr as $name=>$block) {
            foreach ($block as $method=>$func) {
                $this->products[$name . $method] = $func;
            }
        }
    }

    function loadHooks($arr) {
        foreach ($arr as $name=>$block) {
            foreach ($block as $method=>$func) {
                $this->products[$name . $method] = $func;
            }
        }
    }

    
	function trigger($action, $container, $data, $orig)
	{
		if (isset($this->products[$action]))
		{
            return ($this->products[$action])($container, $data, $orig);
		}
	}

}