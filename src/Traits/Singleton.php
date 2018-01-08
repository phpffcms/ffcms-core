<?php

namespace Ffcms\Core\Traits;

/**
 * Class Singleton. Basic structure of singleton pattern.
 * @package Ffcms\Core\Traits
 */
trait Singleton
{
    protected static $instance;

    /**
     * @return static
     */
    final public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            static::boot();
        }
        return static::$instance;
    }
    
    public static function boot() {}

    /**
     * Override constructor
     */
    final private function __construct() {
        $this->init();
    }

    // disable some magic
    protected function init() {}
    final private function __wakeup() {}
    final private function __clone() {}
}