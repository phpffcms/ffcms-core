<?php

namespace Ffcms\Core\Traits;

/**
 * Class Singleton
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
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static;
    }

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