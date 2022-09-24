<?php

namespace GenerCodeOrm;

class Hooks extends Factory {


    function loadHooksFromFile($link) {
        $hook = $this;
        if ($link) {
            if (file_exists($link)) {
                include($link);
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