<?php

namespace Ffcms\Core;

use Core\Exception\NativeException;

class Property {

    protected static $config;

    function __construct()
    {
        $file = root . '/config.php';
        if(file_exists($file) && is_readable($file)) {
            $cfg = @include_once($file);
            if(is_array($cfg) && sizeof($cfg) > 0) {
                self::$config = $cfg;
            }
        } else {
            new NativeException('File config.php not available');
        }
    }

    public function get($config)
    {
        return self::$config[$config];
    }

    public function getAll()
    {
        return self::$config;
    }
}