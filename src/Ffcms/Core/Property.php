<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\NativeException;

/**
 * Class Property - work with system configurations
 * @package Ffcms\Core
 */
class Property
{
    public $version = [
        'num' => '3.0.0',
        'date' => '01.10.2015'
    ];

    protected static $config;

    public function __construct()
    {
        $file = root . '/Private/Config/General.php';
        if (is_file($file) && is_readable($file)) {
            $cfg = @include_once($file);
            if (is_array($cfg) && count($cfg) > 0) {
                self::$config = $cfg;
            }
        } else {
            new NativeException('File /Private/Config/General.php not founded!');
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