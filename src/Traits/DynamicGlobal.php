<?php

namespace Ffcms\Core\Traits;

use Ffcms\Templex\Engine\Vars;

/**
 * Special street magic class for extending MVC model usage $this->undefined from any places.
 * Class DynamicProperty
 * @package Ffcms\Core\Arch\Constructors
 */
trait DynamicGlobal
{
    /**
     * Set global variable for magic callback on MVC apps $this->var = value
     * @param $var
     * @param $value
     */
    final public function __set($var, $value)
    {
        Vars::instance()->setGlobal($var, $value);
    }

    /**
     * Get variable from MVC model on magic callback $this->var
     * @param $var
     * @return mixed|null
     */
    final public function __get($var)
    {
        $globals = Vars::instance()->getGlobalsArray();
        return array_key_exists($var, $globals) ? $globals[$var] : null;
    }

    /**
     * Check if global variable exists for isset and empty methods. In php 7.0.6 without this definition warning occurred.
     * @param string $var
     * @return bool
     */
    final public function __isset($var)
    {
        return Vars::instance()->issetGlobal($var);
    }
}
