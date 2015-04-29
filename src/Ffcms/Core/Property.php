<?php

namespace Ffcms\Core;

use Core\Exception\NativeException;

class Property
{
    protected static $config;

    public function __construct()
    {
        $file = root . '/config.php';
        if (is_file($file) && is_readable($file)) {
            $cfg = @include_once($file);
            if (is_array($cfg) && count($cfg) > 0) {
                self::$config = $cfg;
            }
        } else {
            new NativeException('File config.php not founded!');
        }
    }

    /**
     * Check if configure file is exist and loaded
     * @return bool
     */
    public function isConfigExists()
    {
        return (is_array(self::$config) && count(self::$config) > 0);
    }

    /**
     * Get config value by key
     * @param $config
     * @return mixed
     */
    public function get($config)
    {
        return self::$config[$config];
    }

    /**
     * Get all configs as array
     * @return array
     */
    public function getAll()
    {
        return self::$config;
    }
}