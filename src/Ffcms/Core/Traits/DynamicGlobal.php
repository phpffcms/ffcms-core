<?php

namespace Ffcms\Core\Traits;

use Ffcms\Core\App;

/**
 * Special street magic class for extending MVC model usage $this->undefined from any places.
 * Class DynamicProperty
 * @package Ffcms\Core\Arch\Constructors
 */
trait DynamicGlobal {

    protected $data;

    public final function __set($var, $value)
    {
        App::$Response->setGlobal($var, $value);
    }

    public final function __get($var)
    {
        $globals = App::$Response->getGlobals();
        return $globals[$var];
    }
}