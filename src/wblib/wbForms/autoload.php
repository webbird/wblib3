<?php

#echo "AUTOLOADER LOADED<br />";

spl_autoload_register(function($class) {
#    if(!substr_compare($class, 'wblib\wbForms', 0, 12)) {
        $file = str_replace('\\','/',__DIR__)
              . '/'
              . str_replace(
                    array('wblib\wbForms\\','\\','_'),
                    array('','/','/'),
                    $class
                ).'.php';
#echo "FILE: $file<br />";
        if (file_exists($file))
            @require $file;
#    }
    // next in stack
});