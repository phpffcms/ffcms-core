<?php


namespace Ffcms\Core\Arch\Constructors;
use Core\App;
use Core\Helper\Object;


/**
 * Special street magic class for extending MVC model usage $this->undefined from any places.
 * Class Magic
 * @package Ffcms\Core\Arch\Constructors
 */
abstract class Magic {

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