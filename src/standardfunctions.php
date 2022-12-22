<?php
namespace GenerCodeOrm;

if (! function_exists(__NAMESPACE__ . '\regAutoload')) {

function regAutoload($namespace, $base) {
    //register psr-4 autoload
    spl_autoload_register(function ($class_name) use ($namespace, $base) {
        $parts = explode("\\", $class_name);
        $file = $base .  "/";
        $onamespace = array_shift($parts);
        if ($onamespace == $namespace) {
            $file .= implode("/", $parts) . ".php";
            if (file_exists($file)) {
                require_once($file);
                return;
            }
        }
    });
}


}
