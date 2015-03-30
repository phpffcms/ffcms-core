<?php


namespace Ffcms\Core\Arch\Constructors;
use Core\App;


/**
 * Special street magic class for extending MVC model usage $this->undefined from any places.
 * Class Magic
 * @package Ffcms\Core\Arch\Constructors
 */
abstract class Magic {

    protected $data = [];

    public final function __set($var, $value)
    {
        $this->data[$var] = $value;
    }


    public function __destruct()
    {
        if(sizeof($this->data) > 0) {
            foreach($this->data as $var => $value) {
                App::$Response->setGlobal($var, $value);
            }
        }
    }
}