<?php

namespace Ffcms\Core\Traits;

use Ffcms\Core\Template\Variables;

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
    public final function __set($var, $value)
    {
        Variables::instance()->setGlobal($var, $value);
    }

    /**
     * Get variable from MVC model on magic callback $this->var
     * @param $var
     * @return mixed
     */
    public final function __get($var)
    {
        $globals = Variables::instance()->getGlobalsArray();
        return $globals[$var];
    }
}