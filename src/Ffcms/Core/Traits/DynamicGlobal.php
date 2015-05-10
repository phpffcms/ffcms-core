<?php

namespace Ffcms\Core\Traits;

use Ffcms\Core\Template\Variables;

/**
 * Special street magic class for extending MVC model usage $this->undefined from any places.
 * Class DynamicProperty
 * @package Ffcms\Core\Arch\Constructors
 */
trait DynamicGlobal {

    protected $data;

    public final function __set($var, $value)
    {
        Variables::instance()->setGlobal($var, $value);
    }

    public final function __get($var)
    {
        $globals = Variables::instance()->getGlobalsArray();
        return $globals[$var];
    }
}